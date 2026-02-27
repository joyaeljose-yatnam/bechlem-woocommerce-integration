<?php
/**
 * Base Importer Class
 *
 * @package PrinterSystem
 */

if (!defined('ABSPATH')) {
    exit;
}

abstract class PS_Base_Importer {
    
    /**
     * CSV file path
     *
     * @var string
     */
    protected $file_path;
    
    /**
     * CSV delimiter
     *
     * @var string
     */
    protected $delimiter = PS_CSV_DELIMITER;
    
    /**
     * Import statistics
     *
     * @var array
     */
    protected $stats = [
        'imported' => 0,
        'skipped' => 0,
        'errors' => 0
    ];
    
    /**
     * Constructor
     *
     * @param string $file_path CSV file path
     */
    public function __construct($file_path = null) {
        if ($file_path) {
            $this->file_path = $file_path;
        }
    }
    
    /**
     * Check if CSV file exists
     *
     * @return bool
     */
    protected function file_exists() {
        return file_exists($this->file_path);
    }
    
    /**
     * Open CSV file
     *
     * @return resource|false
     */
    protected function open_csv() {
        if (!$this->file_exists()) {
            return false;
        }
        
        return fopen($this->file_path, 'r');
    }
    
    /**
     * Skip header row
     *
     * @param resource $handle File handle
     */
    protected function skip_header($handle) {
        fgetcsv($handle, 0, $this->delimiter);
    }
    
    /**
     * Get next CSV row
     *
     * @param resource $handle File handle
     * @return array|false
     */
    protected function get_row($handle) {
        return fgetcsv($handle, 0, $this->delimiter);
    }
    
    /**
     * Get import statistics
     *
     * @return array
     */
    public function get_stats() {
        return $this->stats;
    }
    
    /**
     * Display import results
     */
    public function display_results() {
        echo "<h3>Import Complete!</h3>";
        echo "<p>Imported: {$this->stats['imported']}</p>";
        echo "<p>Skipped: {$this->stats['skipped']}</p>";
        echo "<p>Errors: {$this->stats['errors']}</p>";
    }
    
    /**
     * Abstract method - process a single row
     *
     * @param array $row CSV row data
     * @return bool Success status
     */
    abstract protected function process_row($row);
    
    /**
     * Run the import
     *
     * @return bool
     */
    public function run() {
        $handle = $this->open_csv();
        
        if (!$handle) {
            echo "Error: File not found - " . esc_html($this->file_path);
            return false;
        }
        
        $this->skip_header($handle);
        
        while (($row = $this->get_row($handle)) !== FALSE) {
            if ($this->process_row($row)) {
                $this->stats['imported']++;
            } else {
                $this->stats['skipped']++;
            }
        }
        
        fclose($handle);
        
        return true;
    }
}