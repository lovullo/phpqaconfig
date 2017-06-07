<?php
namespace PHP_CodeSniffer\Sniffs;

use PHP_CodeSniffer\Files\File;

class LoVullo_Sniffs_Whitespace_ControlStructureSpacingSniff implements Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = array(
                                   'PHP',
                                   'JS',
                                  );

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
                T_IF,
                T_WHILE,
                T_FOREACH,
                T_FOR,
                T_SWITCH,
                T_DO,
                T_ELSE,
                T_ELSEIF,
               );
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

        if (isset($tokens[$stackPtr]['scope_closer']) === false) {
            return;
        }

        $scopeOpener = $tokens[$stackPtr]['scope_opener'];
        $scopeCloser = $tokens[$stackPtr]['scope_closer'];

        $trailingContent = $phpcsFile->findNext(
            array( T_WHITESPACE, T_COMMENT ),
            ($scopeCloser + 1),
            null,
            true
        );

        // Ignore if the next token is ELSE or ELSEIF, which means this is a IF/ELSEIF/ELSE block
        // Also, if it's a another closing bracket, this is OK, as we are closing a nesting of blocks
        if (in_array($tokens[$trailingContent]['code'], array(T_ELSE, T_ELSEIF, T_CLOSE_CURLY_BRACKET))) {
            return;
        }

        if ($tokens[$trailingContent]['line'] === ($tokens[$scopeCloser]['line'] + 1)) {
            $error = 'No blank line found after control structure';
            $phpcsFile->addError($error, $scopeCloser, 'NeedBlankLineAfterControlStruct');
        }
    }//end process()
}//end class
;
