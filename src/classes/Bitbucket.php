<?php


class Bitbucket
{
    private $url;
    private $data = [];

    public function __construct($url)
    {
        $this->url = $url;
    }

    public static function factory($url)
    {
        return new self($url);
    }

    public function run()
    {
        $this->fetchData($this->url);
        $this->prettyFormat();
    }

    private function fetchData($url)
    {
        $data = file_get_contents($url);
        $data = json_decode($data);
        $this->data = array_merge($this->data, $data->values);
        if(isset($data->next)) {
            $this->fetchData($data->next);
        }
    }

    private function prettyFormat()
    {
        echo '<!DOCTYPE html>'.PHP_EOL;
        echo '<html lang="en">'.PHP_EOL;
        echo '<head><title>Bitbucket API Fetcher</title></head>';
        echo '<body>'.PHP_EOL;
        echo '<table>'.PHP_EOL;
        echo '<thead><tr><th>Filename</th><th>Created</th><th>Size</th></tr><th>Download</th></tr></thead>'.PHP_EOL;
        echo '<tbody>'.PHP_EOL;

        foreach($this->data as $value) {
            echo '<tr>'.PHP_EOL;
            printf(
                '<td>%s</td><td>%s</td><td>%s</td><td><a href="%s">Link</a> (%d)</td>'.PHP_EOL,
                $value->name,
                $this->prettyDate($value->created_on),
                $this->prettyBytes($value->size),
                $value->links->self->href,
                $value->downloads
            );
            echo '</tr>'.PHP_EOL;
        }
        echo '</tbody>'.PHP_EOL;
        echo '</body>'.PHP_EOL;
        echo '</html>'.PHP_EOL;
    }

    private function prettyBytes($bytes, $unit = "", $decimals = 2)
    {
        $units = [
            'B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3, 'TB' => 4,
            'PB' => 5, 'EB' => 6, 'ZB' => 7, 'YB' => 8
        ];

        $value = 0;
        if ($bytes > 0) {
            // Generate automatic prefix by bytes
            // If wrong prefix given
            if (!array_key_exists($unit, $units)) {
                $pow = floor(log($bytes)/log(1024));
                $unit = array_search($pow, $units);
            }

            // Calculate byte value by prefix
            $value = ($bytes/pow(1024,floor($units[$unit])));
        }

        // If decimals is not numeric or decimals is less than 0
        // then set default value
        if (!is_numeric($decimals) || $decimals < 0) {
            $decimals = 2;
        }

        // Format output
        return sprintf('%.' . $decimals . 'f '.$unit, $value);
    }

    private function prettyDate($date)
    {
        try {
            $date = new DateTime($date, new DateTimeZone('Europe/Stockholm'));
            $formattedDate = $date->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return $formattedDate;
    }
}