<?php
namespace PHP_CodeSniffer\Sniffs;

use PHP_CodeSniffer\Files\File;

class LoVullo_Sniffs_Functions_FunctionDeclarationArgumentSpacingSniff implements Sniff
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
     * Processes this test, when one of its tokens is encountered.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token in the
     *                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $functionName = $phpcsFile->findNext(array(T_STRING), $stackPtr);
        $openBracket  = $tokens[$stackPtr]['parenthesis_opener'];
        $closeBracket = $tokens[$stackPtr]['parenthesis_closer'];

        $multiLine = ($tokens[$openBracket]['line'] !== $tokens[$closeBracket]['line']);

        $nextParam = $openBracket;
        $params    = array();
        while (($nextParam = $phpcsFile->findNext(T_VARIABLE, ($nextParam + 1), $closeBracket)) !== false) {
            $nextToken = $phpcsFile->findNext(T_WHITESPACE, ($nextParam + 1), ($closeBracket + 1), true);
            if ($nextToken === false) {
                break;
            }

            $nextCode = $tokens[$nextToken]['code'];

            if ($nextCode === T_EQUAL) {
                if ($tokens[($nextToken + 1)]['code'] === T_WHITESPACE) {
                    $gap   = strlen($tokens[($nextToken + 1)]['content']);
                    $arg   = $tokens[$nextParam]['content'];

                    if ($gap != 1) {
                        $error = "Expected 1 space between default value and equals sign for argument \"$arg\"; $gap found";
                        $phpcsFile->addError($error, $nextToken);
                    }
                } else {
                    $error = "Expected 1 space between default value and equals sign for argument";
                    $phpcsFile->addError($error, $nextToken);
                }
            }

            // Find and check the comma (if there is one).
            $nextComma = $phpcsFile->findNext(T_COMMA, ($nextParam + 1), $closeBracket);
            if ($nextComma !== false) {
                // Comma found.
                if ($tokens[($nextComma - 1)]['code'] === T_WHITESPACE) {
                    $space = strlen($tokens[($nextComma - 1)]['content']);
                    $arg   = $tokens[$nextParam]['content'];
                    $error = "Expected 0 spaces between argument \"$arg\" and comma; $space found";
                    $phpcsFile->addError($error, $nextToken);
                }
            }

            // Take references into account when expecting the
            // location of whitespace.
            if ($phpcsFile->isReference(($nextParam - 1)) === true) {
                $whitespace = $tokens[($nextParam - 2)];
            } else {
                $whitespace = $tokens[($nextParam - 1)];
            }

            if (empty($params) === false) {
                // This is not the first argument in the function declaration.
                $arg = $tokens[$nextParam]['content'];

                if ($whitespace['code'] === T_WHITESPACE) {
                    $gap = strlen($whitespace['content']);

                    // Before we throw an error, make sure there is no type hint.
                    $comma     = $phpcsFile->findPrevious(T_COMMA, ($nextParam - 1));
                    $nextToken = $phpcsFile->findNext(T_WHITESPACE, ($comma + 1), null, true);
                    if ($phpcsFile->isReference($nextToken) === true) {
                        $nextToken++;
                    }

                    if ($nextToken !== $nextParam) {
                        // There was a type hint, so check the spacing between
                        // the hint and the variable as well.
                        $hint = $tokens[$nextToken]['content'];

                        if ($gap !== 1 && $multiLine === false) {
                            $error = "Expected 1 space between type hint and argument \"$arg\"; $gap found";
                            $phpcsFile->addError($error, $nextToken);
                        }

                        if ($multiLine === false) {
                            if ($tokens[($comma + 1)]['code'] !== T_WHITESPACE) {
                                $error = "Expected 1 space between comma and type hint \"$hint\"; 0 found";
                                $phpcsFile->addError($error, $nextToken);
                            } else {
                                $gap = strlen($tokens[($comma + 1)]['content']);
                                if ($gap !== 1) {
                                    $error = "Expected 1 space between comma and type hint \"$hint\"; $gap found";
                                    $phpcsFile->addError($error, $nextToken);
                                }
                            }
                        }
                    } elseif ($multiLine === false && $gap !== 1) {
                        $error = "Expected 1 space between comma and argument \"$arg\"; $gap found";
                        $phpcsFile->addError($error, $nextToken);
                    }//end if
                } else {
                    $error = "Expected 1 space between comma and argument \"$arg\"; 0 found";
                    $phpcsFile->addError($error, $nextToken);
                }//end if
            } else {
                // First argument in function declaration.
                if ($whitespace['code'] === T_WHITESPACE) {
                    $gap = strlen($whitespace['content']);
                    $arg = $tokens[$nextParam]['content'];

                    // Before we throw an error, make sure there is no type hint.
                    $bracket   = $phpcsFile->findPrevious(T_OPEN_PARENTHESIS, ($nextParam - 1));
                    $nextToken = $phpcsFile->findNext(T_WHITESPACE, ($bracket + 1), null, true);
                    if ($phpcsFile->isReference($nextToken) === true) {
                        $nextToken++;
                    }

                    if ($nextToken !== $nextParam) {
                        // There was a type hint, so check the spacing between
                        // the hint and the variable as well.
                        $hint = $tokens[$nextToken]['content'];

                        if ($gap !== 1 && $multiLine === false) {
                            $error = "Expected 1 space between type hint and argument \"$arg\"; $gap found";
                            $phpcsFile->addError($error, $nextToken);
                        }

                        if ($multiLine === false
                            && $tokens[($bracket + 1)]['code'] === T_WHITESPACE
                        ) {
                            $gap   = strlen($tokens[($bracket + 1)]['content']);

                            if ($gap != 1) {
                                $error = "Expected 1 space between opening bracket and type hint \"$hint\"; $gap found";
                                $phpcsFile->addError($error, $nextToken);
                            }
                        }
                    } elseif ($multiLine === false) {
                        if ($gap != 1) {
                            $error = "Expected 1 space between opening bracket and argument \"$arg\"; $gap found";
                            $phpcsFile->addError($error, $nextToken);
                        }
                    }
                }//end if
            }//end if

            $params[] = $nextParam;
        }//end while

        if (empty($params) === true) {
            // There are no parameters for this function.
            if (($closeBracket - $openBracket) !== 1) {
                $space = strlen($tokens[($closeBracket - 1)]['content']);
                $error = "Expected 0 spaces between brackets of function declaration; $space found";
                $phpcsFile->addError($error, $stackPtr);
            }
        } else {
            // There should be exactly one space after the opening parenthesis
            if ($tokens[$openBracket + 1]['code'] !== T_WHITESPACE || strlen($tokens[$openBracket + 1]['content']) != 1) {
                // If the open bracket doesn't have a space after it and it's not the last thing on the line, throw an error
                if ($tokens[$openBracket + 1]['content'] != PHP_EOL) {
                    $error = "Expected 1 space between the opening parenthesis and first argument";
                    $phpcsFile->addError($error, $openBracket);
                }
            }

            // There should be exactly one space or a newline before the closing
            // parenthesis (todo: check for proper indentation if newline)
            if ($tokens[$closeBracket - 1]['code'] !== T_WHITESPACE
                || (
                    strlen($tokens[$closeBracket - 1]['content']) != 1
                    && $tokens[$closeBracket - 2]['content'] !== PHP_EOL
                )
            ) {
                $firstOnLine = $phpcsFile->findFirstOnLine(T_WHITESPACE, $closeBracket, true);

                // Don't throw an error if the closing parenthesis is the first thing on the line
                if ($firstOnLine != $closeBracket) {
                    $error = "Expected 1 space between the last argument and the closing parenthesis";
                    $phpcsFile->addError($error, $closeBracket);
                }
            }
        }
    }//end process()
}//end class
;
