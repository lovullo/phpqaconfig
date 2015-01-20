<?php

/**
 * Squiz_Sniffs_PHP_ForbiddenFunctionsSniff.
 *
 * Discourages the use of debug functions.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   Release: 1.2.2
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class LoVullo_Sniffs_PHP_ForbiddenFunctionsSniff extends Generic_Sniffs_PHP_ForbiddenFunctionsSniff
{
    /**
     * A list of forbidden functions with their alternatives.
     *
     * The value is NULL if no alternative exists. IE, the
     * function should just not be used.
     *
     * @var array(string => string|null)
     */
    public $forbiddenFunctions = array(
                                    'create_function'              => null,
                                    'debug_print_backtrace'        => null,
                                    'delete'                       => 'unset',
                                    'error_log'                    => null,
                                    'error_reporting'              => null,
                                    'getraw'                       => null,
                                    'is_null'                      => null,
                                    'phpinfo'                      => null,
                                    'print'                        => 'echo',
                                    'print_r'                      => null,
                                    'sizeof'                       => 'count',
                                    'var_dump'                     => null,
                                    'xdebug_break'                 => null,
                                    'xdebug_debug_zval'            => null,
                                    'xdebug_debug_zval_stdout'     => null,
                                    'xdebug_disable'               => null,
                                    'xdebug_dump_superglobals'     => null,
                                    'xdebug_enable'                => null,
                                    'xdebug_get_code_coverage'     => null,
                                    'xdebug_get_declared_vars'     => null,
                                    'xdebug_get_function_stack'    => null,
                                    'xdebug_get_headers'           => null,
                                    'xdebug_get_profiler_filename' => null,
                                    'xdebug_get_stack_depth'       => null,
                                    'xdebug_get_tracefile_name'    => null,
                                    'xdebug_is_enabled'            => null,
                                    'xdebug_memory_usage'          => null,
                                    'xdebug_peak_memory_usage'     => null,
                                    'xdebug_print_function_stack'  => null,
                                    'xdebug_start_code_coverage'   => null,
                                    'xdebug_start_trace'           => null,
                                    'xdebug_stop_code_coverage'    => null,
                                    'xdebug_stop_trace'            => null,
                                    'xdebug_time_index'            => null,
                                    'xdebug_var_dump'              => null,
                                );

    /**
     * If true, an error will be thrown; otherwise a warning.
     *
     * @var bool
     */
    public $error = true;
}
