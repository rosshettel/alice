<?php
/*
NAME:         Say cheese!
ABOUT:        Takes a photo from the webcam and stores it in Dropbox. Note: This command will NOT work on Windows. It has only been tested to work on Ubuntu 11.10; it may work on Mac.
DEPENDENCIES: MySQL module;
INSTALL:      You may need to install ffmpeg and ImageMagick if they aren't already;
CONFIG:       Change the location of your webcam and where you would like the image to be stored;
*/
$imgLoc = date('Y/m/d/H-i');
if (!file_exists("/home/jacob/Dropbox/Alice/webcam/".date('Y/m/d/'))) mkdir("/home/jacob/Dropbox/Alice/webcam/".date('Y/m/d/'), 0755, true);
if (!(date('Hi') % 2))
{
	$time = date("M j, Y g:i a");
	exec("ffmpeg -f video4linux2 -i /dev/video0 -vframes 1 /home/jacob/Dropbox/Alice/webcam/$imgLoc.jpg && convert /home/jacob/Dropbox/Alice/webcam/$imgLoc.jpg -gravity southeast -stroke none -fill white -annotate 0 '$time' /home/jacob/Dropbox/Alice/webcam/latest.jpg");
	alice_mysql_putImage("webcam_latest", "/home/jacob/Dropbox/Alice/webcam/latest.jpg", "image/jpg");
}
?>