<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
$options = getopt('', ['command:', 'ce-source:', 'ee-source:', 'help', 'exclude:']);

$command = !empty($options['command']) ? $options['command'] : 'link';
$ce = !empty($options['ce-source'])
    ? realpath($options['ce-source'])
    : realpath(
        __DIR__
        . DIRECTORY_SEPARATOR
        . '..' . DIRECTORY_SEPARATOR
        . '..' . DIRECTORY_SEPARATOR
        . '..' . DIRECTORY_SEPARATOR
        . 'magento2ce'
    );
$ee = !empty($options['ee-source'])
    ? realpath($options['ee-source'])
    : realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
$isExclude = !empty($options['exclude']) ? (boolean)$options['exclude'] : false;
$excludeFile = $ce . DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR . 'info' . DIRECTORY_SEPARATOR . 'exclude';

if (isset($options['help'])) {
    echo "Usage: Magento 2 Based Build script allows you to link Additional Code repository to your Base(Magento) repository.

 --command <link>|<unlink>\tLink or Unlink Additional code\t\tDefault: link
 --ce-source <path/to/magento>\tPath to magento branch clone\t\tDefault: $ce
 --ee-source <path/to/additional-branch>\tPath to any additional resource repository clone (common, or kipling-preproduction) \t\tDefault: $ee
 --exclude <true>|<false>\tExclude symlink files from magento branch\tDefault: false
 --help\t\t\t\tThis help
";
    exit(0);
}

if (!file_exists($ce)) {
    echo "Expected $ce folder not found" . PHP_EOL;
    exit(1);
}

if (!file_exists($ee)) {
    echo "Expected $ee folder not found" . PHP_EOL;
    exit(1);
}

$excludePaths = [];
$unusedPaths = [];

$localePath =  $ee.DIRECTORY_SEPARATOR.'src/magento/app/locale/';
$localePaths = array(
    $localePath.'en_US',
    $localePath.'de_DE',
    $localePath.'en_GB',
    $localePath.'es_ES',
    $localePath.'fr_FR',
    $localePath.'it_IT',
    $localePath.'nl_NL',
);
switch ($command) {
    case 'link':
        foreach (scanFiles($ee) as $filename) {
            $target = preg_replace('#^' . preg_quote($ee) . "#", '', $filename);

            if (!file_exists(dirname($ce . $target))) {
                @symlink(dirname($filename), dirname($ce . $target));
                $excludePaths[] = resolvePath(dirname($target));
            } else if (!file_exists($ce . $target)) {
                if (is_link(dirname($ce . $target))) {
                    continue;
                }
                @symlink($filename, $ce . $target);
                $excludePaths[] = resolvePath($target);
            } else {
                if (in_array($filename,$localePaths)) {
                    foreach (glob($filename . DIRECTORY_SEPARATOR . '*') as $localeFile) {
                        $newSymFile = $ce . $target .DIRECTORY_SEPARATOR. basename($localeFile);
                        if (!file_exists($newSymFile)) {
                            @symlink($localeFile, $newSymFile);
                        }
                    }
                }
                continue;
            }
        }
        /* unlink broken links */
        foreach (scanFiles($ce) as $filename) {
            if (is_link($filename) && !file_exists($filename)) {
                $unusedPaths[] = resolvePath(preg_replace('#^' . preg_quote($ce) . "#", '', $filename));
                unlinkFile($filename);
            }
        }

        setExcludePaths($excludePaths, $unusedPaths, ($isExclude)?$excludeFile:false);

        echo "All symlinks you can see at files:" . PHP_EOL
            . ($isExclude?"Full list\t" . $excludeFile . PHP_EOL . "Updated\t\t":"")
            . realpath(__DIR__ . DIRECTORY_SEPARATOR . 'exclude.log') . PHP_EOL;
        break;

    case 'unlink':
        foreach (scanFiles($ce) as $filename) {

            if (is_link($filename)) {
                $unusedPaths[] = resolvePath(preg_replace('#^' . preg_quote($ce) . "#", '', $filename));
                unlinkFile($filename);
            }
        }
        setExcludePaths($excludePaths, $unusedPaths, ($isExclude)?$excludeFile:false);
        break;
}

/**
 * Create exclude file based on $newPaths and $oldPaths
 *
 * @param array $newPaths
 * @param array $oldPaths
 * @param bool $writeToFile
 * @return void
 */
function setExcludePaths($newPaths, $oldPaths, $writeToFile = false)
{
    if (false != $writeToFile && file_exists($writeToFile)) {
        $content = file($writeToFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($content as $lineNum => $line) {

            $newKey = array_search($line, $newPaths);
            if (false !== $newKey) {
                unset($newPaths[$newKey]);
            }

            $oldKey = array_search($line, $oldPaths);
            if (false !== $oldKey) {
                unset($content[$lineNum]);
            }
        }
        $content = array_merge($content, $newPaths);
        formatContent($content);
        file_put_contents($writeToFile, $content);
    }
    formatContent($newPaths);
    file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'exclude.log', $newPaths);
}

/**
 * Format content before write to file
 *
 * @param array $content
 * @return void
 */
function formatContent(&$content)
{
    array_walk(
        $content,
        function (&$value) {
            $value = resolvePath($value) . PHP_EOL;
        }
    );
}

/**
 * Scan all files from Magento root
 *
 * @param string $path
 * @return array
 */
function scanFiles($path)
{
    $results = [];
    foreach (glob($path . DIRECTORY_SEPARATOR . '*') as $filename) {
        $results[] = $filename;
        if (is_dir($filename)) {
            $results = array_merge($results, scanFiles($filename));
        }
    }
    return $results;
}

/**
 * OS depends unlink
 *
 * @param string $filename
 * @return void
 */
function unlinkFile($filename)
{
    strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && is_dir($filename) ? @rmdir($filename) : @unlink($filename);
}

/**
 * Resolve path to Unix format
 *
 * @param string $path
 * @return string
 */
function resolvePath($path)
{
    return ltrim(str_replace('\\', '/', $path), '/');
}
