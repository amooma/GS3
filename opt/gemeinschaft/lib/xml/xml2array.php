<?php

// put a xml string into array
class XMLThing
{
	var $rawXML;
	var $valueArray = array();
	var $keyArray = array();
	var $parsed = array();
	var $index = 0;
	var $attribKey = 'attributes';
	var $valueKey = 'value';
	var $cdataKey = 'cdata';
	var $isError = false;
	var $error = '';

	function XMLThing($xml = NULL)
	{
		$this->rawXML = $xml;
	}

	function parse($xml = NULL)
	{
		if (!is_null($xml))
		{
			$this->rawXML = $xml;
		}

		$this->isError = false;
			
		if (!$this->parse_init())
		{
			return false;
		}

		$this->index = 0;
		$this->parsed = $this->parse_recurse();
		$this->status = 'parsing complete';

		return $this->parsed;
	}

	function parse_recurse()
	{		
		$found = array();
		$tagCount = array();

		while (isset($this->valueArray[$this->index]))
		{
			$tag = $this->valueArray[$this->index];
			$this->index++;

			if ($tag['type'] == 'close')
			{
				return $found;
			}

			if ($tag['type'] == 'cdata')
			{
				$tag['tag'] = $this->cdataKey;
				$tag['type'] = 'complete';
			}

			$tagName = $tag['tag'];

			if (isset($tagCount[$tagName]))
			{		
				if ($tagCount[$tagName] == 1)
				{
					$found[$tagName] = array($found[$tagName]);
				}
					
				$tagRef =& $found[$tagName][$tagCount[$tagName]];
				$tagCount[$tagName]++;
			}
			else	
			{
				$tagCount[$tagName] = 1;
				$tagRef =& $found[$tagName];
			}

			switch ($tag['type'])
			{
				case 'open':
					$tagRef = $this->parse_recurse();

					if (isset($tag['attributes']))
					{
						$tagRef[$this->attribKey] = $tag['attributes'];
					}
						
					if (isset($tag['value']))
					{
						if (isset($tagRef[$this->cdataKey]))	
						{
							$tagRef[$this->cdataKey] = (array)$tagRef[$this->cdataKey];	
							array_unshift($tagRef[$this->cdataKey], $tag['value']);
						}
						else
						{
							$tagRef[$this->cdataKey] = $tag['value'];
						}
					}
					break;

				case 'complete':
					if (isset($tag['attributes']))
					{
						$tagRef[$this->attribKey] = $tag['attributes'];
						$tagRef =& $tagRef[$this->valueKey];
					}

					if (isset($tag['value']))
					{
						$tagRef = $tag['value'];
					}
					break;
			}			
		}

		return $found;
	}

	function parse_init()
	{
		$this->parser = xml_parser_create();

		$parser = $this->parser;
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);	 
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);		
		if (!$res = (bool)xml_parse_into_struct($parser, $this->rawXML, $this->valueArray, $this->keyArray))
		{
			$this->isError = true;
			$this->error = 'error: '.xml_error_string(xml_get_error_code($parser)).' at line '.xml_get_current_line_number($parser);
		}
		xml_parser_free($parser);

		return $res;
	}
}

?>
