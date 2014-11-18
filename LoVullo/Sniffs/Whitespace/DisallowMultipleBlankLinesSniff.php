<?php
/**
 * This sniff prohibits more than one blank line between anything
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Ralph Wissing <wissingr@lovullo.com>
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * This sniff prohibits more than one blank line between anything
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Ralph Wissing <wissingr@lovullo.com>
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class LoVullo_Sniffs_Whitespace_DisallowMultipleBlankLinesSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array(int)
     */
    public function register()
    {
        return array(T_WHITESPACE);
    }

    /**
     * Processes the tokens that this sniff is interested in.
     *
     * @param  PHP_CodeSniffer_File $phpcsFile The file where the token was found.
     * @param  int                  $stackPtr  The position in the stack where
     *                                         the token was found.
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $matches = array();
        $tokens  = $phpcsFile->getTokens();

        if (strpos($tokens[$stackPtr]['content'], "\n") != strrpos($tokens[$stackPtr]['content'], "\n")) {
            $error = 'Only use 1 blank line between objects';

            $phpcsFile->addError($error, $stackPtr);
        }
    }
}
