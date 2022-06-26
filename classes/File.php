<?php

class File
{
    public $fp;
    public $file;

    public function __construct($file)
    {
        $this->file = $file;
        if (!is_writable($this->file)) {
            echo "Файл {$this->file} недоступен для записи";
            exit;
        }
        $this->fp = fopen($this->file, 'w+');
    }

    public function __destruct()
    {
        fclose($this->fp);
    }

    public function write($text)
    {
        if (fwrite($this->fp, $text . PHP_EOL) === FALSE) {
            echo "Не могу произвести запись в файл {$this->fp}";
            exit;
        }
        echo "Запись в файл {$this->fp} успешно произведена";
    }

}