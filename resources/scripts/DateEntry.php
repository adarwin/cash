<?php
class DateEntry
{
    private $date;
    private $entries = array();

    function __construct($date)
    {
        $this->date = $date;
    }

    function addEntry($text)
    {
        array_push($entries, $text);
    }

    function getDate()
    {
        return $this->date;
    }
}
?>
