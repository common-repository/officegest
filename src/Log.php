<?php

namespace OfficeGest;
use RuntimeException;
class Log
{

    private static $fileName;

	public static function write($message)
	{
		try {
			if (!is_dir(OFFICEGEST_DIR . '/logs') && !mkdir($concurrentDirectory = OFFICEGEST_DIR . '/logs') && !is_dir($concurrentDirectory)) {
				throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
			}
			$logFile = fopen(OFFICEGEST_DIR . '/logs/officegest.log', 'ab');
			fwrite($logFile, '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL);
		} catch (RuntimeException $exception) {

		}
	}

	public static function getFileUrl()
	{
		return OFFICEGEST_DIR . '/logs/officegest.log';
	}

    /**
     * Clear log files
     */
	public static function removeLogs()
	{
		$logFiles = glob(OFFICEGEST_DIR . '/logs/*.log');
		if (!empty($logFiles) && is_array($logFiles)) {
			$deleteSince = strtotime(date('Y-m-d'));
			foreach ($logFiles as $file) {
				if (filemtime($file) < $deleteSince) {
					unlink($file);
				}
			}
		}

	}


	public static function setFileName($name)
    {
        if (!empty($name)) {
            self::$fileName = $name;
        }
    }

}
