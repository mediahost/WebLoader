<?php

namespace WebLoader\Filter;

/**
 * Replace urls in CSS
 *
 * @author Petr Poupě
 * @license MIT
 */
class CssUrlReplacerFilter
{

    private $patterns;
    private $replacement;

    /**
     * @param string $patterns
     * @param string $replacement
     */
    public function __construct($patterns, $replacement, $start = TRUE, $end = FALSE)
    {
        $this->patterns = array();
        
	$arr_patterns = is_array($patterns) ? $patterns : array($patterns);
	foreach ($arr_patterns as $pattern) {
	    $this->patterns[] = "~" . ($start ? "^" : "") . preg_quote($pattern, "~") . ($end ? "$" : "") . "~";
	}

	$this->replacement = $replacement;
    }

    /**
     * Make replacment in url
     * @param string $url image url
     * @param string $quote single or double quote
     * @param string $cssFile absolute css file path
     * @return string
     */
    public function replaceUrl($url, $quote, $cssFile)
    {
	foreach ($this->patterns as $pattern) {
	    $url = preg_replace($pattern, $this->replacement, $url);
	}
	return $url;
    }

    /**
     * Invoke filter
     * @param string $code
     * @param \WebLoader\Compiler $loader
     * @param string $file
     * @return string
     */
    public function __invoke($code, \WebLoader\Compiler $loader, $file = null)
    {
	// thanks to kravco
	$regexp = '~
			(?<![a-z])
			url\(                                     ## url(
				\s*                                   ##   optional whitespace
				([\'"])?                              ##   optional single/double quote
				(   (?: (?:\\\\.)+                    ##     escape sequences
					|   [^\'"\\\\,()\s]+              ##     safe characters
					|   (?(1)   (?!\1)[\'"\\\\,() \t] ##       allowed special characters
						|       ^                     ##       (none, if not quoted)
						)
					)*                                ##     (greedy match)
				)
				(?(1)\1)                              ##   optional single/double quote
				\s*                                   ##   optional whitespace
			\)                                        ## )
		~xs';

	$self = $this;

	return preg_replace_callback($regexp, function ($matches) use ($self, $file) {
	    return "url('" . $self->replaceUrl($matches[2], $matches[1], $file) . "')";
	}, $code);
    }

}
