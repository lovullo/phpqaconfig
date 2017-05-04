<?php

namespace PHP_CodeSniffer\Sniffs;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;

class LoVullo_Sniffs_Functions_FunctionCallSignatureSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_STRING);
    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
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

        // Find the next non-empty token.
        $openBracket = $phpcsFile->findNext(Tokens::$emptyTokens, ($stackPtr + 1), null, true);

        if ($tokens[$openBracket]['code'] !== T_OPEN_PARENTHESIS) {
            // Not a function call.
            return;
        }

        if (isset($tokens[$openBracket]['parenthesis_closer']) === false) {
            // Not a function call.
            return;
        }

        // Find the previous non-empty token.
        $search   = Tokens::$emptyTokens;
        $search[] = T_BITWISE_AND;
        $previous = $phpcsFile->findPrevious($search, ($stackPtr - 1), null, true);
        if ($tokens[$previous]['code'] === T_FUNCTION) {
            // It's a function definition, not a function call.
            return;
        }

        if ($tokens[$previous]['code'] === T_NEW) {
            // We are creating an object, not calling a function.
            return;
        }

        $closeBracket = $tokens[$openBracket]['parenthesis_closer'];

        if (($stackPtr + 1) !== $openBracket) {
            // Checking this: $value = my_function[*](...).
            $error = 'Space before opening parenthesis of function call prohibited';
            $phpcsFile->addError($error, $stackPtr, 'SpaceBeforeOpenBracket');
        }

        $next = $phpcsFile->findNext(T_WHITESPACE, ($closeBracket + 1), null, true);
        if ($tokens[$next]['code'] === T_SEMICOLON) {
            if (in_array($tokens[($closeBracket + 1)]['code'], Tokens::$emptyTokens) === true) {
                $error = 'Space after closing parenthesis of function call prohibited';
                $phpcsFile->addError($error, $closeBracket, 'SpaceAfterCloseBracket');
            }
        }

        // Check if this is a single line or multi-line function call.
        if ($tokens[$openBracket]['line'] === $tokens[$closeBracket]['line']) {
            $this->processSingleLineCall($phpcsFile, $stackPtr, $openBracket, $tokens);
        } else {
            $this->processMultiLineCall($phpcsFile, $stackPtr, $openBracket, $tokens);
        }
    }//end process()


    /**
     * Processes single-line calls.
     *
     * @param File  $phpcsFile   The file being scanned.
     * @param int   $stackPtr    The position of the current token
     *                           in the stack passed in $tokens.
     * @param int   $openBracket The position of the openning bracket
     *                           in the stack passed in $tokens.
     * @param array $tokens      The stack of tokens that make up
     *                           the file.
     *
     * @return void
     */
    public function processSingleLineCall(File $phpcsFile, $stackPtr, $openBracket, $tokens)
    {
        // Find Closing Bracket
        $closeBracket = $tokens[$openBracket]['parenthesis_closer'];

        // Function call with no Arguments- Parenthesis should be right next to each other
        if ($closeBracket == ($openBracket + 1)) {
            return;
        }

        if ($tokens[($openBracket + 1)]['code'] !== T_WHITESPACE) {
            // Checking this: $value = my_function([*]...).
            $error = 'Space needed after opening parenthesis of function call';
            $phpcsFile->addError($error, $stackPtr);
        }

        if ($tokens[($closeBracket - 1)]['code'] !== T_WHITESPACE) {
            // Checking this: $value = my_function(...[*]).
            $error = 'Space needed before closing parenthesis of function call';
            $phpcsFile->addError($error, $closeBracket);
        }
    }

    /**
     * Processes multi-line calls.
     *
     * @param File  $phpcsFile   The file being scanned.
     * @param int   $stackPtr    The position of the current token
     *                           in the stack passed in $tokens.
     * @param int   $openBracket The position of the openning bracket
     *                           in the stack passed in $tokens.
     * @param array $tokens      The stack of tokens that make up
     *                           the file.
     *
     * @return void
     */
    public function processMultiLineCall(File $phpcsFile, $stackPtr, $openBracket, $tokens)
    {
        // We need to work out how far indented the function
        // call itself is, so we can work out how far to
        // indent the arguments.
        $functionIndent = 0;
        for ($i = ($stackPtr - 1); $i >= 0; $i--) {
            if ($tokens[$i]['line'] !== $tokens[$stackPtr]['line']) {
                $i++;
                break;
            }
        }

        if ($tokens[$i]['code'] === T_WHITESPACE) {
            $functionIndent = strlen($tokens[$i]['content']);
        }

        // Each line between the parenthesis should be indented 4 spaces.
        $closeBracket = $tokens[$openBracket]['parenthesis_closer'];
        $lastLine     = $tokens[$openBracket]['line'];
        for ($i = ($openBracket + 1); $i < $closeBracket; $i++) {
            // Skip nested function calls and long arrays
            if ($tokens[$i]['code'] === T_OPEN_PARENTHESIS) {
                $i        = $tokens[$i]['parenthesis_closer'];
                $lastLine = $tokens[$i]['line'];
                continue;
            }
            // Skip nested short arrays
            if ($tokens[$i]['code'] === T_OPEN_SHORT_ARRAY) {
                $i        = $tokens[$i]["bracket_closer"];
                $lastLine = $tokens[$i]['line'];
                continue;
            }

            if ($tokens[$i]['line'] !== $lastLine) {
                $lastLine = $tokens[$i]['line'];

                // Ignore heredoc indentation.
                if (in_array($tokens[$i]['code'], Tokens::$heredocTokens) === true) {
                    continue;
                }

                // Ignore multi-line string indentation.
                if (in_array($tokens[$i]['code'], Tokens::$stringTokens) === true) {
                    if ($tokens[$i]['code'] === $tokens[($i - 1)]['code']) {
                        continue;
                    }
                }

                // We changed lines, so this should be a whitespace indent token, but first make
                // sure it isn't a blank line because we don't need to check indent unless there
                // is actually some code to indent.
                $nextCode = $phpcsFile->findNext(T_WHITESPACE, ($i + 1), ($closeBracket + 1), true);
                if ($tokens[$nextCode]['line'] !== $lastLine) {
                    $error = 'Empty lines are not allowed in multi-line function calls';
                    $phpcsFile->addError($error, $i, 'EmptyLine');
                    continue;
                }

                if ($nextCode === $closeBracket) {
                    // Closing brace needs to be indented to the same level
                    // as the function call.
                    $expectedIndent = $functionIndent;
                } else {
                    $expectedIndent = ($functionIndent + 4);
                }

                if ($tokens[$i]['code'] !== T_WHITESPACE) {
                    $foundIndent = 0;
                } else {
                    $foundIndent = strlen($tokens[$i]['content']);
                }

                if ($expectedIndent !== $foundIndent) {
                    $error = 'Multi-line function call not indented correctly; expected %s spaces but found %s';
                    $data  = array(
                              $expectedIndent,
                              $foundIndent,
                             );
                    $phpcsFile->addError($error, $i, 'Indent', $data);
                }
            }//end if

            // Skip the rest of a closure.
            if ($tokens[$i]['code'] === T_CLOSURE) {
                $i        = $tokens[$i]['scope_closer'];
                $lastLine = $tokens[$i]['line'];
                continue;
            }
        }//end for

        // Opening parenthesis must be the last thing on a line
        // -OR- the line can end in an opening parenthesis and a quote character (for multi-line long SQL strings)
        if ($tokens[($openBracket + 1)]['content'] !== $phpcsFile->eolChar) {
            // Allow multi-line function calls to start with an opening quote
            // on the same line. Otherwise, parameters must be on different lines.
            //
            // Here is a string which should be allowed:
            //
            // $db->query( "
            //      SELECT
            //          *
            //      FROM
            //          Table
            // " );
            //

            $error = 'Opening parenthesis of a multi-line function call must be the last content on the line';

            $open_paren   = $tokens[ $openBracket ];
            $ws_token     = $tokens[ ($openBracket + 1) ];
            $string_token = $tokens[ ($openBracket + 2) ];

            // If next token is not whitespace, throw the error
            if ($ws_token['code'] != T_WHITESPACE) {
                $phpcsFile->addError($error, $stackPtr);

                return;
            }

            // If next-next token isn't a string, throw the error
            if ($string_token['code'] != T_CONSTANT_ENCAPSED_STRING) {
                $phpcsFile->addError($error, $stackPtr);

                return;
            }

            // If whitepsace token isn't on the same line, throw the error
            if ($open_paren['line'] != $ws_token['line']) {
                $phpcsFile->addError($error, $stackPtr);

                return;
            }

            // If the string token isn't on the same line as the parenthesis, throw the error
            if ($open_paren['line'] != $string_token['line']) {
                $phpcsFile->addError($error, $stackPtr);

                return;
            }

            // Second character of the token is the string content (first char is the opening quote)
            $first_char = ord(substr($string_token['content'], 1, 1));

            // If the string content doesn't start with a newline, throw the error
            if ($first_char != 13 && $first_char != 10) {
                $phpcsFile->addError($error, $stackPtr);

                return;
            }
        }

        $prev = $phpcsFile->findPrevious(T_WHITESPACE, ($closeBracket - 1), null, true);
        if ($tokens[$prev]['line'] === $tokens[$closeBracket]['line']) {
            $ws_token     = $tokens[ ($closeBracket - 1) ];
            $string_token = $tokens[ ($closeBracket - 2) ];

            if ($ws_token['code'] != T_WHITESPACE || $string_token['code'] != T_CONSTANT_ENCAPSED_STRING) {
                $error = 'Closing parenthesis of a multi-line function call must be on a line by itself';
                $phpcsFile->addError($error, $closeBracket);
            }
        }
    }//end processMultiLineCall()
}//end class
;
