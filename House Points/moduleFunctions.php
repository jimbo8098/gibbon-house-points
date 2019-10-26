<?php

function showResultAlert($res)
{
    if($res != null && $res != '')
    {
        $divHTML = "";
        switch($res)
        {
            case 0: $divHTML .= "<div class='success'>"; break;
            default: $divHTML .= "<div class='error'>"; break;
        }

        switch($res)
        {
            case 0: $divHTML .= "Points added successfully!"; break;
            default: $divHTML .= "An error occurred"; break;
        }

        $divHTML .= "</div>";
        return $divHTML;
    }
    else
    {
        return "";
    }
}