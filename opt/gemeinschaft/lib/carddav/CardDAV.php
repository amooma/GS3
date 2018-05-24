<?php

/**
 * CardDAV PHP
 *
 * Simple CardDAV query
 * --------------------
 * $carddav = new carddav_backend('https://davical.example.com/user/contacts/');
 * $carddav->set_auth('username', 'password');
 * echo $carddav->get();
 *
 *
 * Simple vCard query
 * ------------------
 * $carddav = new carddav_backend('https://davical.example.com/user/contacts/');
 * $carddav->set_auth('username', 'password');
 * echo $carddav->get_vcard('0126FFB4-2EB74D0A-302EA17F');
 *
 *
 * XML vCard query
 * ------------------
 * $carddav = new carddav_backend('https://davical.example.com/user/contacts/');
 * $carddav->set_auth('username', 'password');
 * echo $carddav->get_xml_vcard('0126FFB4-2EB74D0A-302EA17F');
 *
 *
 * Check CardDAV server connection
 * -------------------------------
 * $carddav = new carddav_backend('https://davical.example.com/user/contacts/');
 * $carddav->set_auth('username', 'password');
 * var_dump($carddav->check_connection());
 *
 *
 * CardDAV delete query
 * --------------------
 * $carddav = new carddav_backend('https://davical.example.com/user/contacts/');
 * $carddav->set_auth('username', 'password');
 * $carddav->delete('0126FFB4-2EB74D0A-302EA17F');
 *
 *
 * CardDAV add query
 * --------------------
 * $vcard = 'BEGIN:VCARD
 * VERSION:3.0
 * UID:1f5ea45f-b28a-4b96-25as-ed4f10edf57b
 * FN:Christian Putzke
 * N:Christian;Putzke;;;
 * EMAIL;TYPE=OTHER:christian.putzke@graviox.de
 * END:VCARD';
 *
 * $carddav = new carddav_backend('https://davical.example.com/user/contacts/');
 * $carddav->set_auth('username', 'password');
 * $vcard_id = $carddav->add($vcard);
 *
 *
 * CardDAV update query
 * --------------------
 * $vcard = 'BEGIN:VCARD
 * VERSION:3.0
 * UID:1f5ea45f-b28a-4b96-25as-ed4f10edf57b
 * FN:Christian Putzke
 * N:Christian;Putzke;;;
 * EMAIL;TYPE=OTHER:christian.putzke@graviox.de
 * END:VCARD';
 *
 * $carddav = new carddav_backend('https://davical.example.com/user/contacts/');
 * $carddav->set_auth('username', 'password');
 * $carddav->update($vcard, '0126FFB4-2EB74D0A-302EA17F');
 *
 *
 * CardDAV debug
 * -------------
 * $carddav = new carddav_backend('https://davical.example.com/user/contacts/');
 * $carddav->enable_debug();
 * $carddav->set_auth('username', 'password');
 * $carddav->get();
 * var_dump($carddav->get_debug());
 *
 *
 * CardDAV server list
 * -------------------
 * DAViCal:						https://example.com/{resource|principal|username}/{collection}/
 * Apple Addressbook Server:	https://example.com/addressbooks/users/{resource|principal|username}/{collection}/
 * memotoo:						https://sync.memotoo.com/cardDAV/
 * SabreDAV:					https://example.com/addressbooks/{resource|principal|username}/{collection}/
 * ownCloud:					https://example.com/apps/contacts/carddav.php/addressbooks/{resource|principal|username}/{collection}/
 * SOGo:						https://example.com/SOGo/dav/{resource|principal|username}/Contacts/{collection}/
 *
 *
 * @author Christian Putzke <christian.putzke@graviox.de>
 * @copyright Christian Putzke
 * @link http://www.graviox.de/
 * @link https://twitter.com/cputzke/
 * @since 20.07.2011
 * @version 0.6
 * @license http://www.gnu.org/licenses/agpl.html GNU AGPL v3 or later
 *
 */

class carddav_backend
{
	/**
	 * CardDAV PHP Version
	 *
	 * @constant	string
	 */
	const VERSION = '0.6';

	/**
	 * User agent displayed in http requests
	 *
	 * @constant	string
	 */
	const USERAGENT = 'CardDAV PHP/';

	/**
	 * CardDAV server url
	 *
	 * @var	string
	 */
	private $url = null;

	/**
	 * CardDAV server url_parts
	 *
	 * @var	array
	 */
	private $url_parts = null;

	/**
	 * Authentication string
	 *
	 * @var	string
	 */
	private $auth = null;

	/**
	* Authentication: username
	*
	* @var	string
	*/
	private $username = null;

	/**
	* Authentication: password
	*
	* @var	string
	*/
	private $password = null;

	/**
	 * Characters used for vCard id generation
	 *
	 * @var	array
	 */
	private $vcard_id_chars = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 'A', 'B', 'C', 'D', 'E', 'F');

	/**
	 * CardDAV server connection (curl handle)
	 *
	 * @var	resource
	 */
	private $curl;

	/**
	 * Debug on or off
	 *
	 * @var	boolean
	 */
	private $debug = false;

	/**
	 * All available debug information
	 *
	 * @var	array
	 */
	private $debug_information = array();

	/**
	 * Exception codes
	 */
	const EXCEPTION_WRONG_HTTP_STATUS_CODE_GET				= 1000;
	const EXCEPTION_WRONG_HTTP_STATUS_CODE_GET_VCARD		= 1001;
	const EXCEPTION_WRONG_HTTP_STATUS_CODE_GET_XML_VCARD	= 1002;
	const EXCEPTION_WRONG_HTTP_STATUS_CODE_DELETE			= 1003;
	const EXCEPTION_WRONG_HTTP_STATUS_CODE_ADD				= 1004;
	const EXCEPTION_WRONG_HTTP_STATUS_CODE_UPDATE			= 1005;
	const EXCEPTION_MALFORMED_XML_RESPONSE					= 1006;
	const EXCEPTION_COULD_NOT_GENERATE_NEW_VCARD_ID			= 1007;


	/**
	 * Constructor
	 * Sets the CardDAV server url
	 *
	 * @param	string	$url	CardDAV server url
	 */
	public function __construct($url = null)
	{
		if ($url !== null)
		{
			$this->set_url($url);
		}
	}

	/**
	 * Sets debug information
	 *
	 * @param	array	$debug_information		Debug information
	 * @return	void
	 */
	public function set_debug(array $debug_information)
	{
		$this->debug_information[] = $debug_information;
	}

	/**
	* Sets the CardDAV server url
	*
	* @param	string	$url	CardDAV server url
	* @return	void
	*/
	public function set_url($url)
	{
		$this->url = $url;

		if (substr($this->url, -1, 1) !== '/')
		{
			$this->url = $this->url . '/';
		}

		$this->url_parts = parse_url($this->url);
	}

	/**
	 * Sets authentication information
	 *
	 * @param	string	$username	CardDAV server username
	 * @param	string	$password	CardDAV server password
	 * @return	void
	 */
	public function set_auth($username, $password)
	{
		$this->username	= $username;
		$this->password	= $password;
		$this->auth		= $username . ':' . $password;
	}

	/**
	 * Gets all available debug information
	 *
	 * @return	array	$this->debug_information	All available debug information
	 */
	public function get_debug()
	{
		return $this->debug_information;
	}

	/**
	 * Gets all vCards including additional information from the CardDAV server
	 *
	 * @param	boolean	$include_vcards		Include vCards within the response (simplified only)
	 * @param	boolean	$raw				Get response raw or simplified
	 * @return	string						Raw or simplified XML response
	 */
	public function get($include_vcards = true, $raw = false)
	{
		$result = $this->query($this->url, 'PROPFIND');

		switch ($result['http_code'])
		{
			case 200:
			case 207:
				if ($raw === true)
				{
					return $result['response'];
				}
				else
				{
					return $this->simplify($result['response'], $include_vcards);
				}
			break;

			default:
				throw new Exception('Woops, something\'s gone wrong! The CardDAV server returned the http status code ' . $result['http_code'] . '.', self::EXCEPTION_WRONG_HTTP_STATUS_CODE_GET);
			break;
		}
	}

	/**
	* Gets a clean vCard from the CardDAV server
	*
	* @param	string	$vcard_id	vCard id on the CardDAV server
	* @return	string				vCard (text/vcard)
	*/
	public function get_vcard($vcard_id)
	{
		$vcard_id	= str_replace('.vcf', null, $vcard_id);
		$result		= $this->query($this->url . $vcard_id . '.vcf', 'GET');

		switch ($result['http_code'])
		{
			case 200:
			case 207:
				return $result['response'];
			break;

			default:
				throw new Exception('Woops, something\'s gone wrong! The CardDAV server returned the http status code ' . $result['http_code'] . '.', self::EXCEPTION_WRONG_HTTP_STATUS_CODE_GET_VCARD);
			break;
		}
	}

	/**
	 * Gets a vCard + XML from the CardDAV Server
	 *
	 * @param	string		$vcard_id	vCard id on the CardDAV Server
	 * @return	string					Raw or simplified vCard (text/xml)
	 */
	public function get_xml_vcard($vcard_id)
	{
		$vcard_id = str_replace('.vcf', null, $vcard_id);

		$xml = new XMLWriter();
		$xml->openMemory();
		$xml->setIndent(4);
		$xml->startDocument('1.0', 'utf-8');
			$xml->startElement('C:addressbook-multiget');
				$xml->writeAttribute('xmlns:D', 'DAV:');
				$xml->writeAttribute('xmlns:C', 'urn:ietf:params:xml:ns:carddav');
				$xml->startElement('D:prop');
					$xml->writeElement('D:getetag');
					$xml->writeElement('D:getlastmodified');
				$xml->endElement();
				$xml->writeElement('D:href', $this->url_parts['path'] . $vcard_id . '.vcf');
			$xml->endElement();
		$xml->endDocument();

		$result = $this->query($this->url, 'REPORT', $xml->outputMemory(), 'text/xml');

		switch ($result['http_code'])
		{
			case 200:
			case 207:
				return $this->simplify($result['response'], true);
			break;

			default:
				throw new Exception('Woops, something\'s gone wrong! The CardDAV server returned the http status code ' . $result['http_code'] . '.', self::EXCEPTION_WRONG_HTTP_STATUS_CODE_GET_XML_VCARD);
			break;
		}
	}

	/**
	 * Enables the debug mode
	 *
	 * @return	void
	 */
	public function enable_debug()
	{
		$this->debug = true;
	}

	/**
	* Checks if the CardDAV server is reachable
	*
	* @return	boolean
	*/
	public function check_connection()
	{
		$result = $this->query($this->url, 'OPTIONS');

		if ($result['http_code'] === 200)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Cleans the vCard
	 *
	 * @param	string	$vcard	vCard
	 * @return	string	$vcard	vCard
	 */
	private function clean_vcard($vcard)
	{
		$vcard = str_replace("\t", null, $vcard);

		return $vcard;
	}

	/**
	 * Deletes an entry from the CardDAV server
	 *
	 * @param	string	$vcard_id	vCard id on the CardDAV server
	 * @return	boolean
	 */
	public function delete($vcard_id)
	{
		$result = $this->query($this->url . $vcard_id . '.vcf', 'DELETE');

		switch ($result['http_code'])
		{
			case 204:
				return true;
			break;

			default:
				throw new Exception('Woops, something\'s gone wrong! The CardDAV server returned the http status code ' . $result['http_code'] . '.', self::EXCEPTION_WRONG_HTTP_STATUS_CODE_DELETE);
			break;
		}
	}

	/**
	 * Adds an entry to the CardDAV server
	 *
	 * @param	string	$vcard		vCard
	 * @param	string	$vcard_id	vCard id on the CardDAV server
	 * @return	string			The new vCard id
	 */
	public function add($vcard, $vcard_id = null)
	{
		if ($vcard_id === null)
		{
			$vcard_id	= $this->generate_vcard_id();
		}
		$vcard		= $this->clean_vcard($vcard);
		$result		= $this->query($this->url . $vcard_id . '.vcf', 'PUT', $vcard, 'text/vcard');

		switch($result['http_code'])
		{
			case 201:
				return $vcard_id;
			break;

			default:
				throw new Exception('Woops, something\'s gone wrong! The CardDAV server returned the http status code ' . $result['http_code'] . '.', self::EXCEPTION_WRONG_HTTP_STATUS_CODE_ADD);
			break;
		}
	}

	/**
	 * Updates an entry to the CardDAV server
	 *
	 * @param	string	$vcard		vCard
	 * @param	string	$vcard_id	vCard id on the CardDAV server
	 * @return	boolean
	 */
	public function update($vcard, $vcard_id)
	{
		try
		{
			return $this->add($vcard, $vcard_id);
		}
		catch (Exception $e)
		{
			throw new Exception($e->getMessage(), self::EXCEPTION_WRONG_HTTP_STATUS_CODE_UPDATE);
		}
	}

	/**
	 * Simplify CardDAV XML response
	 *
	 * @param	string	$response			CardDAV XML response
	 * @param	boolean	$include_vcards		Include vCards or not
	 * @return	string						Simplified CardDAV XML response
	 */
	private function simplify($response, $include_vcards = true)
	{
		$response = $this->clean_response($response);

		try
		{
			$xml = new SimpleXMLElement($response);
		}
		catch(Exception $e)
		{
			throw new Exception('The XML response seems to be malformed and can\'t be simplified!', self::EXCEPTION_MALFORMED_XML_RESPONSE, $e);
		}

		$simplified_xml = new XMLWriter();
		$simplified_xml->openMemory();
		$simplified_xml->setIndent(4);

		$simplified_xml->startDocument('1.0', 'utf-8');
			$simplified_xml->startElement('response');

				if (!empty($xml->response))
				{
					foreach ($xml->response as $response)
					{
						if (preg_match('/vcard/', $response->propstat->prop->getcontenttype) || preg_match('/vcf/', $response->href))
						{
							$id = basename($response->href);
							$id = str_replace('.vcf', null, $id);

							if (!empty($id))
							{
								$simplified_xml->startElement('element');
									$simplified_xml->writeElement('id', $id);
									$simplified_xml->writeElement('etag', str_replace('"', null, $response->propstat->prop->getetag));
									$simplified_xml->writeElement('last_modified', $response->propstat->prop->getlastmodified);

									if ($include_vcards === true)
									{
										$simplified_xml->writeElement('vcard', $this->get_vcard($id));
									}
								$simplified_xml->endElement();
							}
						}
						else if (preg_match('/unix-directory/', $response->propstat->prop->getcontenttype))
						{
							if (isset($response->propstat->prop->href))
							{
								$href = $response->propstat->prop->href;
							}
							else if (isset($response->href))
							{
								$href = $response->href;
							}
							else
							{
								$href = null;
							}

							$url = str_replace($this->url_parts['path'], null, $this->url) . $href;
							$simplified_xml->startElement('addressbook_element');
								$simplified_xml->writeElement('display_name', $response->propstat->prop->displayname);
								$simplified_xml->writeElement('url', $url);
								$simplified_xml->writeElement('last_modified', $response->propstat->prop->getlastmodified);
							$simplified_xml->endElement();
						}
					}
				}

			$simplified_xml->endElement();
		$simplified_xml->endDocument();

		return $simplified_xml->outputMemory();
	}

	/**
	 * Cleans CardDAV XML response
	 *
	 * @param	string	$response	CardDAV XML response
	 * @return	string	$response	Cleaned CardDAV XML response
	 */
	private function clean_response($response)
	{
		$response = utf8_encode($response);
		$response = str_replace('D:', null, $response);
		$response = str_replace('d:', null, $response);
		$response = str_replace('C:', null, $response);
		$response = str_replace('c:', null, $response);

		return $response;
	}

	/**
	 * Curl initialization
	 *
	 * @return void
	 */
	public function curl_init()
	{
		if (empty($this->curl))
		{
			$this->curl = curl_init();
			curl_setopt($this->curl, CURLOPT_HEADER, true);
			curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($this->curl, CURLOPT_USERAGENT, self::USERAGENT.self::VERSION);

			if ($this->auth !== null)
			{
				curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
				curl_setopt($this->curl, CURLOPT_USERPWD, $this->auth);
			}
		}
	}

	/**
	 * Query the CardDAV server via curl and returns the response
	 *
	 * @param	string	$url				CardDAV server URL
	 * @param	string	$method				HTTP method like (OPTIONS, GET, HEAD, POST, PUT, DELETE, TRACE, COPY, MOVE)
	 * @param	string	$content			Content for CardDAV queries
	 * @param	string	$content_type		Set content type
	 * @return	array						Raw CardDAV Response and http status code
	 */
	private function query($url, $method, $content = null, $content_type = null)
	{
		$this->curl_init();

		curl_setopt($this->curl, CURLOPT_URL, $url);
		curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $method);

		if ($content !== null)
		{
			curl_setopt($this->curl, CURLOPT_POST, true);
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, $content);
		}
		else
		{
			curl_setopt($this->curl, CURLOPT_POST, false);
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, null);
		}

		if ($content_type !== null)
		{
			curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Content-type: '.$content_type));
		}
		else
		{
			curl_setopt($this->curl, CURLOPT_HTTPHEADER, array());
		}

		$complete_response	= curl_exec($this->curl);
		$header_size		= curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
		$http_code 			= curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
		$header				= trim(substr($complete_response, 0, $header_size));
		$response			= substr($complete_response, $header_size);

		$return = array(
			'response'		=> $response,
			'http_code'		=> $http_code
		);

		if ($this->debug === true)
		{
			$debug = $return;
			$debug['url']			= $url;
			$debug['method']		= $method;
			$debug['content']		= $content;
			$debug['content_type']	= $content_type;
			$debug['header']		= $header;
			$this->set_debug($debug);
		}

		return $return;
	}

	/**
	 * Returns a valid and unused vCard id
	 *
	 * @return$carddav->query($this->url . $vcard_id . '.vcf', 'GET');

			if ($result['http_code'] !== 404)
			{
				$vcard_id = $this->generate_vcard_id();
			}

			return $vcard_id;
		}
		catch (Exception $e)
		{
			throw new Exception($e->getMessage(), self::EXCEPTION_COULD_NOT_GENERATE_NEW_VCARD_ID);
		}
	}

	/**
	 * Destructor
	 * Close curl connection if it's open
	 *
	 * @return	void
	 */
	public function __destruct()
	{
		if (!empty($this->curl))
		{
			curl_close($this->curl);
		}
	}
}
