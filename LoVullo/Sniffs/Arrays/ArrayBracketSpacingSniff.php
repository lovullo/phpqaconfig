<?php

class LoVullo_Sniffs_Arrays_ArrayBracketSpacingSniff implements PHP_CodeSniffer_Sniff
{
    public function register()
    {
        return array(
                T_OPEN_SQUARE_BRACKET,
                T_CLOSE_SQUARE_BRACKET,
               );
    }

    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if ($tokens[ $stackPtr ]['type'] === 'T_OPEN_SQUARE_BRACKET') {
            // Previous token
            $prevType = $tokens[ ($stackPtr - 1) ]['code'];

            // Open brackets can never be preceded by a space
            if (in_array($prevType, PHP_CodeSniffer_Tokens::$emptyTokens) === true) {
                $nonSpace = $phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, ($stackPtr - 2), null, true);
                $expected = $tokens[ $nonSpace ]['content'].$tokens[ $stackPtr ]['content'];
                $found    = $phpcsFile->getTokensAsString($nonSpace, ($stackPtr - $nonSpace)).$tokens[ $stackPtr ]['content'];
                $error    = 'Space found before square bracket; expected "%s" but found "%s"';
                $data     = array(
                             $expected,
                             $found,
                         );

                $phpcsFile->addError($error, $stackPtr, 'SpaceBeforeBracket', $data);
            }

            // Open brackets must be followed by the space if a PHP variable follows
            if ($tokens[ ($stackPtr + 1) ]['type'] == "T_VARIABLE") {
                $var_token = ($stackPtr + 1);
                $expected  = $tokens[ $stackPtr ]['content']." ".$tokens[ $var_token ]['content'];
                $found     = $tokens[ $stackPtr ]['content'].$tokens[ $var_token ]['content'];
                $error     = 'No space found after square bracket; expected "%s" but found "%s"';
                $data      = array(
                             $expected,
                             $found,
                            );

                $phpcsFile->addError($error, $stackPtr, 'SpaceAfterBracket', $data);
            }

            // Open brackets can be followed by a space, but no more than a single space!
            if ($tokens[ ($stackPtr + 1) ]['type'] == "T_WHITESPACE"
                && strlen($tokens[ ($stackPtr + 1) ]['content']) > 1) {
                $ws_token  = ($stackPtr + 1);
                $expected  = $tokens[ $stackPtr ]['content']." ";
                $found     = $tokens[ $stackPtr ]['content'].$tokens[ $ws_token ]['content'];
                $error     = 'Too many spaces found after square bracket; expected "%s" but found "%s"';
                $data      = array(
                             $expected,
                             $found,
                            );

                $phpcsFile->addError($error, $stackPtr, 'TooManySpacesAfterBracket', $data);
            }
        }

        if ($tokens[ $stackPtr ]['type'] === 'T_CLOSE_SQUARE_BRACKET') {
            // Don't check for space after the close bracket; There are too
            // many possibilities that should be covered by other rules
            // anyways.

            $opening_bracket = $tokens[ $stackPtr ]["bracket_opener"];
            $space_before    = ($tokens[ ($opening_bracket + 1) ]['type'] == "T_WHITESPACE") ? true : false;
            $space_after     = ($tokens[ ($stackPtr - 1) ]['type'] == "T_WHITESPACE") ? true : false;

            // If there is a space before the array key, there must be a space after and the other way around
            if ($space_before !== $space_after) {
                if ($space_before === true) {
                    $error = "There was no space before the closing bracket, but space was found after the opening bracket. Spacing must be balanced around array keys.";
                } else {
                    $error = "There was no space after the opening bracket, but space was found before closing bracket. Spacing must be balanced around array keys.";
                }

                $phpcsFile->addError($error, $stackPtr, "BraceSpacingMustBeBalanced");
            }

            // There can be a space before the closing bracket, but no more than a single space
            if ($tokens[ ($stackPtr - 1) ]['type'] == "T_WHITESPACE"
                && strlen($tokens[ ($stackPtr - 1) ]['content']) > 1) {
                $ws_token  = ($stackPtr - 1);
                $expected  = " ".$tokens[ $stackPtr ]['content'];
                $found     = $tokens[ $ws_token ]['content'].$tokens[ $stackPtr ]['content'];
                $error     = 'Too many spaces found after square bracket; expected "%s" but found "%s"';
                $data      = array(
                             $expected,
                             $found,
                            );

                $phpcsFile->addError($error, $stackPtr, 'TooManySpacesBeforeBracket', $data);
            }
        }
    }
}
