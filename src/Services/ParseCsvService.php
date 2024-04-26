<?php

namespace App\Services;
use \ForceUTF8\Encoding;

class ParseCsvService 
{
    /**
     * Retrieves an array of lines from a given file path.
     *
     * @param string $filePath The path to the file.
     * @return array An array of lines from the file.
     */
    public static function getLines(string $filePath): array
    {
        $lines = [];
        
        if (!file_exists($filePath)) return $lines;

        $fileStream  = fopen($filePath, 'r+');
      
        while (($data = fgetcsv($fileStream)) != false) { 
            if (is_null($data[0])) continue; // skip empty lines

            foreach ($data as $key => $item) {
                $item = Encoding::toUTF8($item); // convert to UTF-8, if not already UTF-8
        
                // i dont want to reject the whole file if only one symbol is invali, so i'd rather make it empty if value is not vonvertable
                if (!mb_check_encoding($item, 'UTF-8')) $item = '';

                $data[$key] = trim($item);
            }          

            $lines[] = $data;
        }
    
        return $lines;
    }
 
    /**
     * Retrieves full data based on given lines and field names.
     *
     * @param array $lines The lines of data to process.
     * @param array $field_names The names of the fields to extract.
     * @return array The full data extracted based on the given lines and field names.
     */
    public static function getFullData(array $lines, array $fieldNames): array
    {
        $data = [];

        foreach ($lines as $line) {
            if (!$line) continue; // skip empty array just in case, but technically in scv even if line empty array has at least one element

            $fields = $line;
            $res = [];

            foreach ($fieldNames as $key => $header) {
                //convertion of values to camelCase in not nessesary but makes code more consistent
                $newKey = self::camelize(strtolower(trim($header))); 

                if (!array_key_exists($key, $fields)) continue;

                $res[$newKey] = $fields[$key];        
            }

            $data[] = $res;
        }

        return $data;
    }

    /**
     * Eliminates empty strings from the target file, replaces all delimeters to '\n', and saves the result to a temporary file.
     *
     * @param string $targetFilePath The path to the target file.
     * @param string $tempFilePath The path to the temporary file.
     * @return void
     */
    public function eliminateEmptyStrings(string $targetFilePath, string $tempFilePath): void
    {
        file_put_contents($tempFilePath,
        preg_replace(
            '/\R+/',
            "\n",
            trim(file_get_contents($targetFilePath))
        ));
    }

    /**
     * Converts a string to camelCase.
     *
     * @param string $word The input string to convert to camelCase
     * @return string The camelCase converted string
     */
    public static function camelize(string $word): string
    {
        return str_replace(' ', '', ucwords(preg_replace('/[^A-Za-z0-9]+/', ' ', $word))); 
    }

    /**
     * Returns an array containing the converted file size value and its corresponding measure in a human-readable format.
     *
     * @param int $bytes The size of the file in bytes.
     * @param int $precision The number of decimal places to round the converted value to. Default is 2.
     * @return array An array with the keys 'value' and 'mesure', where 'value' is the converted file size rounded to the specified precision, and 'mesure' is the corresponding measure (B, KB, MB, GB, or TB).
     */
    public function getConvertedFileSizeFormat(int $bytes, int $precision = 2): array
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB']; 
   
        $bytes = max($bytes, 0); 
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow = min($pow, count($units) - 1); 
       
        return ['value' => round($bytes, $precision), 'mesure' => $units[$pow]]; 
    }

    /**
     * Checks if the given file size is too big.
     *
     * @param int $bytes The size of the file in bytes.
     * @return bool Returns true if the file size is over 3 GB, false otherwise.
     */
    public function fileIsTooBig(int $bytes): bool
    {
        $convertedBytes = self::getConvertedFileSizeFormat($bytes);

        return $convertedBytes['mesure'] == 'TB' || ($convertedBytes['mesure'] == 'GB' && $convertedBytes['value'] > 3);
    }
}