<?php
namespace PHP_CodeSniffer\Sniffs;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;

class LoVullo_Sniffs_Whitespace_FunctionSpacingSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_FUNCTION);
    }//end register()


    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token
     *                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        /*
            Check the number of blank lines
            after the function.
        */

        // Scope level; Scope level 0 is a global function
        $level = $tokens[$stackPtr]["level"];

        if (isset($tokens[$stackPtr]['scope_closer']) === false) {
            // Must be an interface method, so the closer is the semi-colon.
            $closer = $phpcsFile->findNext(T_SEMICOLON, $stackPtr);
        } else {
            $closer = $tokens[$stackPtr]['scope_closer'];
        }

        // There needs to be 2 blank lines after the closer.
        $nextLineToken = null;
        for ($i = $closer; $i < $phpcsFile->numTokens; $i++) {
            if (strpos($tokens[$i]['content'], $phpcsFile->eolChar) === false) {
                continue;
            } else {
                $nextLineToken = ($i + 1);
                break;
            }
        }

        if (is_null($nextLineToken) === true) {
            // Never found the next line, which means
            // there are 0 blank lines after the function.
            $foundLines = 0;
        } else {
            $nextContent = $phpcsFile->findNext(array(T_WHITESPACE), ($nextLineToken + 1), null, true);
            if ($nextContent === false) {
                // We are at the end of the file.
                $foundLines = 0;
            } else {
                $foundLines = ($tokens[$nextContent]['line'] - $tokens[$nextLineToken]['line']);
            }
        }

        // don't require spacing if the previous token is a closing brace,
        // which will be the opening class brace
        if (isset($tokens[$nextLineToken]['content']) && $tokens[$nextLineToken]['content'] !== '}') {

            // "2 lines after function" rule doesn't apply to global functions
            if ($foundLines !== 2 && $level > 0) {
                $phpcsFile->addError("Expected 2 blank lines after function; $foundLines found", $closer, 'LinesBetweenFunctions');
            }
        }

        /*
            Check the number of blank lines
            before the function.
        */

        $prevLineToken = null;
        for ($i = $stackPtr; $i > 0; $i--) {
            if (strpos($tokens[$i]['content'], $phpcsFile->eolChar) === false) {
                continue;
            } else {
                $prevLineToken = $i;
                break;
            }
        }

        if (is_null($prevLineToken) === true) {
            // Never found the previous line, which means
            // there are 0 blank lines before the function.
            $foundLines = 0;
        } else {
            $searchTokens = array_merge( Tokens::$commentTokens, array( T_WHITESPACE ) );
            $prevContent  = $phpcsFile->findPrevious( $searchTokens, $prevLineToken, null, true);

            // Before we throw an error, check that we are not throwing an error
            // for another function. We don't want to error for no blank lines after
            // the previous function and no blank lines before this one as well.
            $currentLine = $tokens[$stackPtr]['line'];
            $prevLine    = ($tokens[$prevContent]['line'] - 1);
            $i           = ($stackPtr - 1);
            $foundLines  = 0;
            while ($currentLine != $prevLine && $currentLine > 1 && $i > 0) {
                if (isset($tokens[$i]['scope_condition']) === true) {
                    $scopeCondition = $tokens[$i]['scope_condition'];
                    if ($tokens[$scopeCondition]['code'] === T_FUNCTION) {
                        // Found a previous function.
                        return;
                    }
                } elseif ($tokens[$i]['code'] === T_FUNCTION) {
                    // Found another interface function.
                    return;
                }

                $currentLine = $tokens[$i]['line'];
                if ($currentLine === $prevLine) {
                    break;
                }

                if ($tokens[($i - 1)]['line'] < $currentLine && $tokens[($i + 1)]['line'] > $currentLine) {
                    // This token is on a line by itself. If it is whitespace, the line is empty.
                    if ($tokens[$i]['code'] === T_WHITESPACE) {
                        $foundLines++;
                    }
                }

                $i--;
            }//end while
        }//end if

        // Does not apply to global functions
        if ($foundLines !== 2 && $level > 0) {
            // Don't throw the error if the previous token was a opening curly brace; This brace should
            // be the opening brace of a class and two padding spaces is unneeded
            if ($tokens[$prevContent]['content'] !== '{') {
                $phpcsFile->addError("Expected 2 blank lines before function; $foundLines found", $stackPtr, 'LinesBetweenFunctions');
            }
        }
    }//end process()
}//end class
;
