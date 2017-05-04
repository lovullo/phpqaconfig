<?php

namespace PHP_CodeSniffer\Sniffs;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Exceptions\RuntimeException;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Functions\OpeningFunctionBraceBsdAllmanSniff;

class LoVullo_Sniffs_Functions_FunctionDeclarationSniff implements Sniff
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
     * @param int  $stackPtr  The position of the current token
     *                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Check if this is a single line or multi-line declaration.
        $openBracket  = $tokens[$stackPtr]['parenthesis_opener'];
        $closeBracket = $tokens[$stackPtr]['parenthesis_closer'];
        if ($tokens[$openBracket]['line'] === $tokens[$closeBracket]['line']) {
            $this->processSingleLineDeclaration($phpcsFile, $stackPtr, $tokens);
        } else {
            $this->processMultiLineDeclaration($phpcsFile, $stackPtr, $tokens);
        }
    }//end process()


    /**
     * Processes single-line declarations.
     *
     * Just uses the Generic BSD-Allman brace sniff.
     *
     * @param File  $phpcsFile The file being scanned.
     * @param int   $stackPtr  The position of the current token
     *                         in the stack passed in $tokens.
     * @param array $tokens    The stack of tokens that make up
     *                         the file.
     *
     * @return void
     */
    public function processSingleLineDeclaration(File $phpcsFile, $stackPtr, $tokens)
    {
        $sniff = new OpeningFunctionBraceBsdAllmanSniff();
        $sniff->process($phpcsFile, $stackPtr);
    }//end processSingleLineDeclaration()


    /**
     * Processes mutli-line declarations.
     *
     * @param File  $phpcsFile The file being scanned.
     * @param int   $stackPtr  The position of the current token
     *                         in the stack passed in $tokens.
     * @param array $tokens    The stack of tokens that make up
     *                         the file.
     *
     * @return void
     */
    public function processMultiLineDeclaration(File $phpcsFile, $stackPtr, $tokens)
    {
        // We need to work out how far indented the function
        // declaration itself is, so we can work out how far to
        // indent parameters.
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
        $openBracket  = $tokens[$stackPtr]['parenthesis_opener'];
        $closeBracket = $tokens[$stackPtr]['parenthesis_closer'];
        $lastLine     = $tokens[$openBracket]['line'];
        for ($i = ($openBracket + 1); $i < $closeBracket; $i++) {
            if ($tokens[$i]['line'] !== $lastLine) {
                if ($tokens[$i]['line'] === $tokens[$closeBracket]['line']) {
                    // Closing brace needs to be indented to the same level
                    // as the function.
                    $expectedIndent = $functionIndent;
                } else {
                    $expectedIndent = ($functionIndent + 4);
                }

                // We changed lines, so this should be a whitespace indent token.
                if ($tokens[$i]['code'] !== T_WHITESPACE) {
                    $foundIndent = 0;
                } else {
                    $foundIndent = strlen($tokens[$i]['content']);
                }

                if ($expectedIndent !== $foundIndent) {
                    $error = "Multi-line function declaration not indented correctly; expected $expectedIndent spaces but found $foundIndent";
                    $phpcsFile->addError($error, $i);
                }

                $lastLine = $tokens[$i]['line'];
            }
        }//end for

        if (isset($tokens[$stackPtr]['scope_opener']) === true) {
            // The openning brace needs to be one space away
            // from the closing parenthesis.
            $next = $tokens[($closeBracket + 1)];
            if ($next['code'] !== T_WHITESPACE) {
                $length = 0;
            } elseif ($next['content'] === $phpcsFile->eolChar) {
                $length = 1;
            } else {
                $length = strlen($next['content']);
            }

            if ($length !== 1) {
                $error = 'There must be a single space between the closing parenthesis and the opening brace of a multi-line function declaration; found ';
                if ($length === -1) {
                    $error .= 'newline';
                } else {
                    $error .= "$length spaces";
                }

                $phpcsFile->addError($error, ($closeBracket + 1));

                return;
            }

            // And just in case they do something funny before the brace...
            $next = $phpcsFile->findNext(
                T_WHITESPACE,
                ($closeBracket + 1),
                null,
                true
            );

            if ($next !== false && $tokens[$next]['code'] !== T_OPEN_CURLY_BRACKET) {
                $error = 'There must be a single space between the closing parenthesis and the opening brace of a multi-line function declaration';
                $phpcsFile->addError($error, $next);
            }
        }//end if

        // The closing parenthesis must be on a new line, even
        // when checking abstract function definitions.
        $prev = $phpcsFile->findPrevious(
            T_WHITESPACE,
            ($closeBracket - 1),
            null,
            true
        );

        if ($tokens[$prev]['line'] === $tokens[$closeBracket]['line']) {
            $error = 'The closing parenthesis of a multi-line function declaration must be on a new line';
            $phpcsFile->addError($error, $closeBracket);
        }
    }//end processMultiLineDeclaration()
}//end class
;
