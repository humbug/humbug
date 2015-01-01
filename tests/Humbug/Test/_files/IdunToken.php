<?php

/**
 * Idun
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled with this
 * package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@flabben.net so we
 * can send you a copy immediately.
 *
 * @category  Idun
 * @package   Idun_Form
 * @copyright Copyright (c) 2010 Arvid Bergelmir (http://www.flabben.net/)
 * @version   $Id:$
 */

/**
 * @category  Idun
 * @package   Idun_Form
 * @copyright Copyright (c) 2010 Arvid Bergelmir
 * @author    Arvid Bergelmir <arvid.bergelmir@flabben.net>
 */
class Idun_Form_Helper_Token
{
    /**
     * @access protected
     * @var    integer
     */
    protected $_tokenLength = 32;
    
    /**
     * @access protected
     * @var    string
     */
    protected $_tokenKey = 'formToken';
    
    /**
     * @access protected
     * @var    string
     */
    protected $_sessionNamespace = 'Idun_Form_Helper_Token';
    
    /**
     * @access protected
     * @var    integer
     */
    protected $_maximumTokenCount = 10;
    
    /**
     * @access public
     * @param  Zend_Config|array|null $config
     * @throws Idun_Form_Helper_Exception
     * @return void
     */
    public function __construct($config = null)
    {
        if ($config instanceof Zend_Config) {
            $this->setConfig($config);
        } elseif (is_array($config)) {
            $this->setOptions($config);
        }
    }
    
    /**
     * @access public
     * @param  Zend_Config $config
     * @return Idun_Form_Helper_Token
     */
    public function setConfig(Zend_Config $config)
    {
        $this->setOptions($config->toArray());
        return $this;
    }
    
    /**
     * @access public
     * @param  array $options
     * @throws Idun_Form_Helper_Exception
     * @return Idun_Form_Helper_Token
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (!method_exists($this, $method)) {
                throw new Idun_Form_Helper_Exception(sprintf(
                    'Unknown option "%s".', $key
                ));
            }
            $this->$method($value);
        }
        return $this;
    }
    
    
    /**
     * @access public
     * @param  integer $tokenLength
     * @param  Idun_Form_Helper_Exception
     * @return Idun_Form_Helper_Token
     */
    public function setTokenLength($tokenLength)
    {
        if ($tokenLength < 6) {
            throw new Idun_Form_Helper_Exception(
                'Token length must be greater or equal 6.'
            );
        }
        $this->_tokenLength = (int)$tokenLength;
        return $this;
    }
    
    /**
     * @access public
     * @return integer
     */
    public function getTokenLength()
    {
        return $this->_tokenLength;
    }
    
    /**
     * @access public
     * @param  string $tokenKey
     * @throws Idun_Form_Helper_Exception
     * @return Idun_Form_Helper_Token
     */
    public function setTokenKey($tokenKey)
    {
        $tokenKey = (string)$tokenKey;
        if (!preg_match('/^[a-z0-9_]+$/i', $tokenKey)) {
            throw new Idun_Form_Helper_Exception(sprintf(
                'Token key "%s" should only contain alphanumeric characters and underscores.',
                $tokenKey
            ));
        }
        $this->_tokenKey = (string)$tokenKey;
        return $this;
    }
    
    /**
     * @access public
     * @return string
     */
    public function getTokenKey()
    {
        return $this->_tokenKey;
    }
    
    /**
     * @access public
     * @param  string $namespace
     * @return Idun_Form_Helper_Token
     */
    public function setSessionNamespace($namespace)
    {
        $this->_sessionNamespace = (string)$namespace;
        return $this;
    }
    
    /**
     * @access public
     * @return string
     */
    public function getSessionNamespace()
    {
        return $this->_sessionNamespace;
    }
    
    /**
     * @access public
     * @param  integer $maximumTokenCount
     * @throws Idun_Form_Helper_Exception
     * @return Idun_Form_Helper_Token
     */
    public function setMaximumTokenCount($maximumTokenCount)
    {
        if ($maximumTokenCount < 1) {
            throw new Idun_Form_Helper_Exception(
                'Maximum token count must be greater or equal 1.'
            );
        }
        $this->_maximumTokenCount = (int)$maximumTokenCount;
        return $this;
    }
    
    /**
     * @access public
     * @return integer
     */
    public function getMaximumTokenCount()
    {
        return $this->_maximumTokenCount;
    }
    
    /**
     * @access public
     * @param  string $token
     * @return Idun_Form_Helper_Token
     */
    public function addToken($token)
    {
        if (!$this->hasToken($token))
        {
            $session  = $this->_getSession();
            $tokens   = $session->tokens;
            $tokens[] = $token;
            
            $maximumTokenCount = $this->getMaximumTokenCount();
            if (($currentTokenCount = count($tokens)) > $maximumTokenCount) {
                $deleteTokenCount = $currentTokenCount - $maximumTokenCount;
                for ($delete = 0; $delete < $deleteTokenCount; ++$delete) {
                    unset($tokens[min(array_keys($tokens))]);
                }
            }
            
            $session->tokens = array_values($tokens);
        }
        return $this;
    }
    
    /**
     * @access public
     * @param  string  $token
     * @param  integer &$index
     * @return boolean
     */
    public function hasToken($token, &$index = null)
    {
        return ($index = array_search(
            $token,
            $this->_getSession()->tokens
        )) !== false;
    }
    
    /**
     * @access public
     * @param  string $token
     * @return Idun_Form_Helper_Token
     */
    public function removeToken($token)
    {
        if ($this->hasToken($token, $index)) {
            unset($this->_getSession()->tokens[$index]);
        }
        return $this;
    }
    
    /**
     * @access public
     * @param  integer|null $length
     * @return string
     */
    public function createToken($tokenLength = null)
    {
        if ($tokenLength === null) {
            $tokenLength = $this->getTokenLength();
        }
        
        $allowedCharacters =
            'abcdefghijklmnopqrstuvwxyz' .
            'ABCDEFGHIJKLMNOPQRSTUVWXYZ' .
            '0123456789';
        
        list($usec, $sec) = explode(' ', microtime());
        srand((float)$sec + ((float)$usec * 10000));
        
        $token = '';
        for ($charCount = 0; $charCount < $tokenLength; ++$charCount) {
            $token .= $allowedCharacters[rand(0, strlen($allowedCharacters) - 1)];
        }
        
        return $token;
    }
    
    /**
     * @access public
     * @param  string      $content
     * @param  string|null $token
     * @return string
     */
    public function parseTokenIntoHtml($html, $token = null)
    {
        if ($token === null) {
            $token = $this->createToken();
        } else {
            $token = str_replace('"', '', $token);
        }
        
        $html = preg_replace(
            '/<form[^>]+method="post"[^>]*>/i',
            sprintf(
                '$0<div><input type="hidden" name="%s" value="%s" /></div>',
                $this->getTokenKey(), $token
            ),
            $html,
            -1,
            $replacementCount
        );

        if ($replacementCount > 0) {
            $this->addToken($token);
        }
        
        return $html;
    }
    
    /**
     * @access protected
     * @return Zend_Session_Namespace
     */
    protected function _getSession()
    {
        static $session = null;
        if ($session === null) {
            $session = new Zend_Session_Namespace(
                $this->getSessionNamespace(), true
            );
        }
        
        if (!isset($session->tokens) || !is_array($session->tokens)) {
            $session->tokens = array();
        }
        
        return $session;
    }
}
