<?php

class LoVullo_Sniffs_Commenting_BlockCommentSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
                T_COMMENT,
                T_DOC_COMMENT,
               );
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The current file being scanned.
     * @param int                  $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // If its an inline comment (//) return.
        if (substr($tokens[$stackPtr]['content'], 0, 2) !== '/*') {
            return;
        }

        // If this is a function/class/interface doc block comment, skip it.
        // We are only interested in inline doc block comments.
        if ($tokens[$stackPtr]['code'] === T_DOC_COMMENT) {
            $nextToken = $phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($stackPtr + 1), null, true);

            $ignore    = array(
                          T_CLASS,
                          T_INTERFACE,
                          T_FUNCTION,
                          T_PUBLIC,
                          T_PRIVATE,
                          T_PROTECTED,
                          T_STATIC,
                          T_ABSTRACT,
                          T_CONST,
                         );

            if (in_array($tokens[$nextToken]['code'], $ignore) === true) {
                return;
            }

            $prevToken = $phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, ($stackPtr - 1), null, true);
            if ($tokens[$prevToken]['code'] === T_OPEN_TAG) {
                return;
            }
        }

        $commentLines = array($stackPtr);
        $nextComment  = $stackPtr;
        $lastLine     = $tokens[$stackPtr]['line'];

        // Construct the comment into an array.
        while (($nextComment = $phpcsFile->findNext($tokens[$stackPtr]['code'], ($nextComment + 1), null, false)) !== false) {
            if (($tokens[$nextComment]['line'] - 1) !== $lastLine) {
                // Not part of the block.
                break;
            }

            $lastLine       = $tokens[$nextComment]['line'];
            $commentLines[] = $nextComment;
        }

        // Small Comments
        if (count($commentLines) <= 2) {
            if (count($commentLines) === 1 && $tokens[$stackPtr]['content'] !== '/**#@-*/') {
                if (strpos($tokens[$stackPtr]['content'], '{{{') !== false ||
                    strpos($tokens[$stackPtr]['content'], '}}}') !== false) {
                    $error = 'No editor-specific turds allowed.';
                    $phpcsFile->addError($error, $stackPtr);
                } else {
                    $error = 'Single line block comment not allowed; use inline ("// text") comment instead';
                    $phpcsFile->addError($error, $stackPtr);
                }

                return;
            }

            if (isset($commentLines[1]) && isset($tokens[$commentLines[1]])) {
                if (trim($tokens[$commentLines[1]]['content']) === '*/') {
                    if (trim($tokens[$stackPtr]['content']) === '/*') {
                        $error = 'Empty block comment not allowed';
                        $phpcsFile->addError($error, $stackPtr);

                        return;
                    }
                }
            }
        }

        $content = trim($tokens[$stackPtr]['content']);
        if ($content !== '/*' && $content !== '/**' && $content !== '/**#@+' && $content !== '/**#@-*/') {
            $error = 'Block comment text must start on a new line';
            $phpcsFile->addError($error, $stackPtr);

            return;
        }
    }
}
