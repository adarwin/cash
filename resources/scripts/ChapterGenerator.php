<?php header('Content-Type: text/plain; charset=utf-8');
//include 'functions.php'
include "Chapter.php";
include "DateEntry.php";

function echoLine($lineToEcho)
{
    echo "$lineToEcho<br>";
}

class ChapterGenerator
{
    private $textFilePath;
    private $currentDate;
    private $currentChapterText;
    private $currentChapter;

    private $chapters = array();

    function __construct($filePath)
    {
        $this->textFilePath = $filePath;
        echoLine("Constructed ChapterGenerator with '$this->textFilePath'");
    }

    // -------- Main parsing function ------

    function parseChapterTextFile()
    {
        echoLine("Opening text file");
        if (file_exists($this->textFilePath))
        {
            $chapterTextFileLines = file($this->textFilePath);
            if (!$chapterTextFileLines)
            {
                // Failed to read Chapters text file
                echoLine("Failed to read Chapters text file");
                return;
            }
            $inTableOfContents = true;
            $chapterOutput = "";
            $foundBOM = false;
            $currentDateEntry;
            $currentEntry;
            foreach ($chapterTextFileLines as $line)
            {
                $trimmedLine = trim($line);
                if (!$foundBOM && $this->startsWithBOM($trimmedLine))
                {
                    $foundBOM = true;
                    // Remove the first 3 characters
                    $trimmedLine = substr($trimmedLine, 3);
                }
                // Check for table of contents
                if ($this->isChapterEntry($trimmedLine))
                {
                    $chapterNumber = $this->getChapterNumber($trimmedLine);
                    $chapterTitle = $this->getChapterTitle($trimmedLine);
                    if ($inTableOfContents)
                    {
                        // Check to see if we have already added this chapter
                        if ($this->chapters[$chapterNumber] != null)
                        {
                            // We have already added this chapter, which means this entry must
                            // contain the actual chapter data.
                            $inTableOfContents = false;
                            $this->setCurrentChapter($chapterNumber);
                        }
                        else
                        {
                            echoLine("setting chapter $chapterNumber");
                            $chapter = new Chapter($chapterNumber, $chapterTitle);
                            $this->chapters[$chapterNumber] = $chapter;
                            //$chapter = new Chapter($chapterNumber, $chapterTitle);
                            //$num = $chapter->getNumber();
                            //$title = $chapter->getTitle();
                            //echoLine("Chapter $num: $title");
                            //echoLine("Chapter $chapter->number: $chapter->title: '$trimmedLine'");
                            //$this->chapters.add(new Chapter("title", "number"));
                            //echoLine("Chapter Definition: '$trimmedLine'");
                        }
                    }
                    else
                    {
                        $this->writeChapter($this->currentChapter);
                        $this->setCurrentChapter($chapterNumber);
                    }
                }
                else if ($this->isStartOfNewDay($trimmedLine))
                {
                    // Indicate a new day has started
                    $currentDate = $this->extractDate($trimmedLine);
                    $textPortion = $this->extractTextFromDateEntry($trimmedLine);
                    $currentDateEntry = new DateEntry($currentDate);
                    $currentDateEntry->addEntry($textPortion);
                    $date = $currentDateEntry->getDate();
                    $inTableOfContents = false;
                }
                else if ($this->isEmptyLine($trimmedLine))
                {
                    //echoLine("Empty Line: '$trimmedLine'");
                }
                else
                {
                    $currentDateEntry->addEntry($trimmedLine);
                    $date = $currentDateEntry->getDate();
                    echoLine("$date - $trimmedLine");
                }
            }
        }
        else
        {
            echo "'$this->textFilePath' does not exist";
        }
    }

    function writeChapter($chapter)
    {
        $chapterNumber = $chapter->getNumber();
        $chapterTitle = $chapter->getTitle();
        echoLine("Writing Chapter $chapterNumber: $chapterTitle");
        // Iterate through date entries
    }

    function writeDateStart($date)
    {
    }

    function writeDateEnd($date)
    {
    }

    // ----------- Helper Methods ---------
    private function extractDate($trimmedLine)
    {
        // Date should be the first thing in the trimmed line
        // Get the index of the first space
        // Account for the following cases
        // M/D
        // MM/DD
        // MM/DD/YY
        // MM/DD/YYYY
        $spaceIndex = strpos($trimmedLine, " ");
        if ($spaceIndex > 0 && $spaceIndex <= 10)
        {
            $date = substr($trimmedLine, 0, $spaceIndex);
        }
        else
        {
            $date = "";
        }
        return $date;
    }

    private function extractTextFromDateEntry($trimmedLine)
    {
        $spaceIndex = strpos($trimmedLine, " ");
        $text = substr($trimmedLine, $spaceIndex);
        $text = trim($text);
        return $text;
    }

    private function getChapterNumber($trimmedChapterLine)
    {
        // Extract the "chapter" part
        $chapterRemoved = substr($trimmedChapterLine, strlen("chapter"));
        // Remove any whitespace before the number
        $chapterRemoved = trim($chapterRemoved);
        // Number should now be at the beginning of the string.
        // Find the colon
        $colonPos = strpos($chapterRemoved, ":");
        $chapterNumber = substr($chapterRemoved, 0, $colonPos);
        // Trim the chapter number, just in case there was a space before the colon
        $chapterNumber = trim($chapterNumber);
        return $chapterNumber;
    }

    private function getChapterTitle($trimmedChapterLine)
    {
        // Extract the colon
        $colonPos = strpos($trimmedChapterLine, ":");
        $chapterName = substr($trimmedChapterLine, $colonPos + 1);
        // Trim chapter name, in case there was a space between the colon and the chapter name
        $chapterName = trim($chapterName);
        return $chapterName;
    }

    private function isChapterEntry($trimmedLine)
    {
        // If this line starts with "chapter", then it is a chapter entry
        //$pos = stripos($trimmedLine, "Chapter");
        if ($this->stringStartsWithSubstringIgnoreCase($trimmedLine, "Chapter"))
        {
            //echoLine("Was a chapter entry");
            return true;
        }
        else
        {
            //echoLine("Was not a chapter entry");
            return false;
        }
    }

    private function isEmptyLine($trimmedLine)
    {
        $length = strlen($trimmedLine);
        if ($length == 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    private function isStartOfNewDay($trimmedLine)
    {
        // If the first 3 characters contain a slash, then this is the start of a new day
        $slashPosition = strpos($trimmedLine, "/");
        if ($slashPosition > 0 && $slashPosition <= 2)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    private function readNextLine($fileContent, &$currentPosition)
    {
        $nextCRLFPosition = strpos($fileContent, "\n", $currentPosition);
        $nextLine = substr($fileContent, $currentPosition, $nextCRLFPosition);
        $currentPosition = $nextCRLFPosition+1;
        return $nextLine;
    }

    private function setCurrentChapter($chapterNumber)
    {
        $this->currentChapter = $this->chapters[$chapterNumber];
        $title = $this->currentChapter->getTitle();
        $number = $this->currentChapter->getNumber();
        echoLine("\nCurent Chapter: Chapter $number - $title");
    }

    private function startsWithBOM($trimmedLine)
    {
        return $this->stringStartsWithSubstringIgnoreCase($trimmedLine, "\xef\xbb\xbf");
    }

    private function stringStartsWithSubstringIgnoreCase($string, $substring)
    {
        // Need to use === when checking return value from stripos
        //echo stripos($string, $substring);
        return stripos($string, $substring) === 0;
    }
}
?>
