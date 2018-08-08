<?php

namespace PHP_CodeSniffer\Sniffs;

/**
 * Verifies that control statements conform to their coding standards.
 */
class LoVullo_Sniffs_ControlStructures_ControlSignatureSniff extends AbstractPatternSniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = array('PHP', 'JS');


    /**
     * Returns the patterns that this test wishes to verify.
     *
     * @return array(string)
     */
    protected function getPatterns()
    {
        return array(
                'try EOL{EOL...EOL}EOLcatch (...)EOL{EOL',
                'doEOL{EOL...EOL} while (...);EOL',
                'while (...)EOL{EOL',
                'for (...)EOL{EOL',
                'if (...)EOL{EOL',
                'foreach (...)EOL{EOL',
                '}EOLelseif (...)EOL{EOL',
               );
    }
}
