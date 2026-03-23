<?php
/**
 * Pagination Class
 * A simple pagination class for handling database record pagination
 */
class Pagination {
    private $currentPage;
    private $itemsPerPage;
    private $totalItems;
    private $totalPages;
    private $offset;

    /**
     * Constructor
     * 
     * @param int $currentPage The current page number
     * @param int $itemsPerPage Number of items to display per page
     * @param int $totalItems Total number of items in the database
     */
    public function __construct($currentPage = 1, $itemsPerPage = 10, $totalItems = 0) {
        $this->currentPage = (int)$currentPage;
        $this->itemsPerPage = (int)$itemsPerPage;
        $this->totalItems = (int)$totalItems;
        
        // Calculate total pages
        $this->totalPages = $this->totalItems > 0 ? ceil($this->totalItems / $this->itemsPerPage) : 1;
        
        // Make sure current page is valid
        if ($this->currentPage < 1) {
            $this->currentPage = 1;
        } elseif ($this->currentPage > $this->totalPages) {
            $this->currentPage = $this->totalPages;
        }
        
        // Calculate the offset for SQL LIMIT clause
        $this->offset = ($this->currentPage - 1) * $this->itemsPerPage;
    }

    /**
     * Get the SQL LIMIT offset
     * 
     * @return int The offset for SQL LIMIT clause
     */
    public function getOffset() {
        return $this->offset;
    }

    /**
     * Get the current page number
     * 
     * @return int The current page number
     */
    public function getCurrentPage() {
        return $this->currentPage;
    }

    /**
     * Get the total number of pages
     * 
     * @return int The total number of pages
     */
    public function getTotalPages() {
        return $this->totalPages;
    }

    /**
     * Check if there are previous pages
     * 
     * @return bool True if there are previous pages, false otherwise
     */
    public function hasPrevious() {
        return $this->currentPage > 1;
    }

    /**
     * Check if there are next pages
     * 
     * @return bool True if there are next pages, false otherwise
     */
    public function hasNext() {
        return $this->currentPage < $this->totalPages;
    }

    /**
     * Get the previous page number
     * 
     * @return int The previous page number
     */
    public function getPreviousPage() {
        return max(1, $this->currentPage - 1);
    }

    /**
     * Get the next page number
     * 
     * @return int The next page number
     */
    public function getNextPage() {
        return min($this->totalPages, $this->currentPage + 1);
    }

    /**
     * Get an array of page numbers to display
     * 
     * @param int $range Number of pages to show before and after current page
     * @return array Array of page objects with properties: number and isSeparator
     */
    public function getPages($range = 2) {
        $pages = [];
        $from = max(1, $this->currentPage - $range);
        $to = min($this->totalPages, $this->currentPage + $range);
        
        // Add first page if not in range
        if ($from > 1) {
            $pages[] = [
                'number' => 1,
                'isSeparator' => false
            ];
            if ($from > 2) {
                $pages[] = [
                    'number' => '...',
                    'isSeparator' => true
                ];
            }
        }
        
        // Add range of pages
        for ($i = $from; $i <= $to; $i++) {
            $pages[] = [
                'number' => $i,
                'isSeparator' => false
            ];
        }
        
        // Add last page if not in range
        if ($to < $this->totalPages) {
            if ($to < $this->totalPages - 1) {
                $pages[] = [
                    'number' => '...',
                    'isSeparator' => true
                ];
            }
            $pages[] = [
                'number' => $this->totalPages,
                'isSeparator' => false
            ];
        }
        
        return $pages;
    }

    /**
     * Generate pagination links HTML
     * 
     * @param string $url The base URL for pagination links
     * @param int $range Number of page links to show around current page
     * @return string HTML for pagination links
     */
    public function createLinks($url, $range = 2) {
        $links = '';
        
        // Add query string separator if not already present
        if (strpos($url, '?') === false) {
            $url .= '?';
        } else {
            $url .= '&';
        }
        
        // Remove existing page parameter if any
        $url = preg_replace('/&page=[0-9]+/', '', $url);
        
        // Previous link
        if ($this->hasPrevious()) {
            $links .= '<li class="page-item"><a class="page-link" href="' . $url . 'page=' . ($this->currentPage - 1) . '">«</a></li>';
        } else {
            $links .= '<li class="page-item disabled"><a class="page-link" href="#">«</a></li>';
        }
        
        // Page links
        $from = max(1, $this->currentPage - $range);
        $to = min($this->totalPages, $this->currentPage + $range);
        
        // First page link if not in range
        if ($from > 1) {
            $links .= '<li class="page-item"><a class="page-link" href="' . $url . 'page=1">1</a></li>';
            if ($from > 2) {
                $links .= '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
            }
        }
        
        // Page number links
        for ($i = $from; $i <= $to; $i++) {
            if ($i == $this->currentPage) {
                $links .= '<li class="page-item active"><a class="page-link" href="#">' . $i . '</a></li>';
            } else {
                $links .= '<li class="page-item"><a class="page-link" href="' . $url . 'page=' . $i . '">' . $i . '</a></li>';
            }
        }
        
        // Last page link if not in range
        if ($to < $this->totalPages) {
            if ($to < $this->totalPages - 1) {
                $links .= '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
            }
            $links .= '<li class="page-item"><a class="page-link" href="' . $url . 'page=' . $this->totalPages . '">' . $this->totalPages . '</a></li>';
        }
        
        // Next link
        if ($this->hasNext()) {
            $links .= '<li class="page-item"><a class="page-link" href="' . $url . 'page=' . ($this->currentPage + 1) . '">»</a></li>';
        } else {
            $links .= '<li class="page-item disabled"><a class="page-link" href="#">»</a></li>';
        }
        
        return '<ul class="pagination justify-content-center">' . $links . '</ul>';
    }
}
?> 