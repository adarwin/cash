<?php
class Chapter
{
    private $number;
    private $title;
    private $dateEntries;
    private $currentDate;

    function __construct($chapterNumber, $chapterTitle)
    {
        $this->number = $chapterNumber;
        $this->title = $chapterTitle;
    }

    function getNumber()
    {
        return $this->number;
    }

    function getTitle()
    {
        return $this->title;
    }
}
?>
