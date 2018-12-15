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
        echo '<html>'.PHP_EOL;
        echo '<head><title>Index of /</title></head>'.PHP_EOL;
        echo '<body bgcolor="white">'.PHP_EOL;
        echo '<h1>Index of /</h1><hr><pre><a href="../">../</a>'.PHP_EOL;
        foreach($this->data as $value) {
            $this->printRow($value);
        }
        echo '</pre><hr></body>'.PHP_EOL;
        echo '</html>'.PHP_EOL;
    }
//helix/                                             25-Nov-2015 23:03                   -
//superrepo.kodi.isengard.all-0.7.04.zip             25-Nov-2015 23:03               57027

    private function printRow($value) {
        echo $this->prettyName($value->name, $value->links->self->href);
        echo $this->prettyDate($value->created_on);
        //echo $this->prettyBytes($value->size);
        echo str_pad($value->size, 20, ' ', STR_PAD_LEFT);
        echo PHP_EOL;
    }

    private function prettyName($name, $href)
    {
        $padLen = (51 - strlen($name));
        $toReturn = '<a href="' . $href . '">'.$name.'</a>';
        $toReturn .= str_repeat(' ', $padLen);
        return $toReturn;
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
            $formattedDate = $date->format('Y-M-d H:i');
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return $formattedDate;
    }
}