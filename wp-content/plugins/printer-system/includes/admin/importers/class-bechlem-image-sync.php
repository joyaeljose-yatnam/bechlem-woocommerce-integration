<?php
/**
 * Bechlem Image Sync Module
 * Handles iterative batch downloading and results tracking.
 */

class Bechlem_Image_Sync {

    private $api_base_url;
    private $batch_limit;
    private $image_format;

    // Result Tracking Variables
    private $success_count = 0;
    private $failed_count  = 0;
    private $failed_ids    = [];

    public function __construct() {
        $this->api_base_url = defined('PS_PICTURE_BASE_URL') ? PS_PICTURE_BASE_URL : "https://api.bechlem.de/v12/picture";
        $this->batch_limit  = defined('PS_PICTURE_SYNC_BATCH_LIMIT') ? PS_PICTURE_SYNC_BATCH_LIMIT : 20;
        $this->image_format = defined('PS_PICTURE_IMAGE_FORMAT') ? PS_PICTURE_IMAGE_FORMAT : "jpg";
    }

    /**
     * 1. THE FULL SYNC LOOP
     */
    public function run_full_sync() {
        // Allow long runs and try to disable buffering so browser gets live updates
        set_time_limit(0);
        @apache_setenv('no-gzip', '1');
        ini_set('zlib.output_compression', 0);
        if (function_exists('ob_get_level')) {
            while (ob_get_level()) {
                @ob_end_flush();
            }
        }
        @ob_implicit_flush(true);
        header('Content-Type: text/html; charset=UTF-8');
        // helpful for some proxies
        header('X-Accel-Buffering: no');

        // container for progressive output
        echo '<div class="bechlem-sync-live" style="font-family:monospace; padding:10px;">';
        echo '<h2>Sync Progress</h2>';
        echo '<div id="bechlem-sync-lines">';
        // make sure initial content reaches browser
        echo str_repeat(' ', 1024);
        flush();

        do {
            $processed_in_this_batch = $this->run_batch_sync();
            sleep(1);
        } while ($processed_in_this_batch > 0);

        echo '</div>'; // #bechlem-sync-lines
        // final summary
        $this->display_results();
        echo '</div>'; // .bechlem-sync-live
        return $this->success_count;
    }

    /**
     * 2. THE BATCH SYNC
     */
    public function run_batch_sync() {
        $args = [
            'post_type'      => 'product',
            'posts_per_page' => $this->batch_limit,
            'fields'         => 'ids',
            'meta_query'     => [
                // only grab products that don’t already have a featured image
                [
                    'key'     => '_thumbnail_id',
                    'compare' => 'NOT EXISTS',
                ],
            ],
        ];

        $product_ids = get_posts( $args );

        if ( empty( $product_ids ) ) {
            return 0;
        }

        $batch_processed = 0;
        foreach ( $product_ids as $product_id ) {
            // use the post ID for the Bechlem API; if you ever store a
            // custom _bechlem_id meta value, it will be used instead.
            $bechlem_id = $this->get_supply_bechlem_id($product_id);
            if ( ! $bechlem_id ) {
                $bechlem_id = $product_id;
            }

            $title = get_the_title( $product_id );

            $url = "{$this->api_base_url}?iditem={$bechlem_id}";

            $result = $this->sideload_image($product_id, $url, $title);

            if (!is_wp_error($result) && $result) {
                $this->success_count++;
                $this->output_progress($product_id, 'OK', $url);
            } else {
                $this->failed_count++;
                $this->failed_ids[] = $product_id;
                $err_msg = is_wp_error($result) ? $result->get_error_message() : 'unknown';
                $this->output_progress($product_id, 'FAILED', $url . ' — ' . $err_msg);
            }

            $batch_processed++;
        }

        return $batch_processed;
    }

    /**
     * 3. DISPLAY RESULTS
     * Generates a clean HTML summary of the sync operation.
     */
    public function display_results() {
        $html = '<div class="bechlem-sync-results" style="padding: 20px; background: #fff; border: 1px solid #ccd0d4; margin-top: 20px;">';
        $html .= '<h2>Sync Summary</h2>';
        $html .= '<ul>';
        $html .= '<li>✅ <strong>Successfully Synced:</strong> ' . $this->success_count . ' images</li>';
        $html .= '<li>❌ <strong>Failed:</strong> ' . $this->failed_count . ' items</li>';
        $html .= '</ul>';

        if (!empty($this->failed_ids)) {
            $html .= '<h3>Failed Product IDs:</h3>';
            $html .= '<p style="color: #d63638;">' . implode(', ', $this->failed_ids) . '</p>';
            $html .= '<p><small>Check your error_log for specific API or permission errors.</small></p>';
        }

        $html .= '</div>';
        echo $html;
    }

    /**
     * Small helper to output per-product progress and flush to browser
     */
    private function output_progress($product_id, $status, $note = '') {
        $color = ($status === 'OK') ? '#0a0' : '#d63638';
        $time = date('H:i:s');
        $line = sprintf(
            '<div style="padding:4px 0;"><strong>[%s]</strong> Product ID %d — <span style="color:%s;font-weight:bold;">%s</span>%s</div>',
            esc_html($time),
            intval($product_id),
            esc_attr($color),
            esc_html($status),
            $note ? ' — ' . esc_html($note) : ''
        );
        echo $line;
        // small flush to push data to client
        flush();
        if (function_exists('usleep')) { usleep(50000); } // optional tiny pause to help streaming
    }

    /**
     * 4. THE SIDELOADER
     */
    private function sideload_image($product_id, $url, $title) {
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $attachment_id = media_sideload_image($url, $product_id, $title, 'id');

        if (!is_wp_error($attachment_id)) {
            set_post_thumbnail($product_id, $attachment_id);
            return $attachment_id;
        }
        
        return $attachment_id; // WP_Error returned if failed
    }

    private function get_supply_bechlem_id($product_id) {
        $meta_value = get_post_meta($product_id, PS_META_OLD_SLUG, true);

        if (!$meta_value) {
            return false;
        }

        return str_replace(PS_IMPORT_PLACEHOLDER_PREFIX, '', $meta_value);
    }
}