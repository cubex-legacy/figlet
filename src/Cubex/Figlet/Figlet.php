<?php
/**
 * @author  brooke.bryan
 */

namespace Cubex\Figlet;

class Figlet
{
  protected $_fontFile;
  protected $_signature;
  protected $_hardblank;
  protected $_height;
  protected $_baseline;
  protected $_maxWidth;
  protected $_defaultSmush;
  protected $_commentLines;
  protected $_rightToLeft;
  protected $_fontSmush;
  protected $_loaded = false;
  protected $_defaultFont = 'standard';

  /**
   * @param null $loadFont
   * @param bool $loadAsDefault
   */
  public function __construct($loadFont = null, $loadAsDefault = true)
  {
    if($loadFont !== null)
    {
      if($loadAsDefault)
      {
        $this->loadDefaultFont($loadFont);
      }
      else
      {
        $this->loadDefaultFont($loadFont);
      }
    }
  }

  /**
   * Helper for default fonts
   *
   * @param        $name
   * @param null   $fontDir
   * @param string $ext
   */
  public function loadDefaultFont($name, $fontDir = null, $ext = '.flf')
  {
    if($fontDir === null)
    {
      $fontDir = __DIR__ . DIRECTORY_SEPARATOR . '.fonts' . DIRECTORY_SEPARATOR;
    }
    $this->loadFont($fontDir . $name . $ext);
  }

  /**
   * @param $fontFile string full path to figlet font file
   *
   * @throws \Exception
   */
  public function loadFont($fontFile)
  {
    $this->_loaded   = false;
    $this->_fontFile = file($fontFile);
    if(!$this->_fontFile)
    {
      throw new \Exception("Could not load '" . $fontFile . "'", 404);
    }

    $definitions = sscanf(
      $this->_fontFile[0],
      '%5s%c %d %*d %d %d %d %d %d',
      $this->_signature,
      $this->_hardblank,
      $this->_height,
      $this->_maxWidth,
      $this->_defaultSmush,
      $this->_commentLines,
      $this->_rightToLeft,
      $this->_fontSmush
    );

    if($this->_signature != "flf2a" || $definitions < 5)
    {
      throw new \Exception("Invalid figlet font file provided");
    }

    $this->_loaded = true;
  }

  /**
   * Get a single character
   *
   * @param $character
   *
   * @return array
   */
  public function getCharacter($character)
  {
    if(!$this->_loaded)
    {
      $this->loadDefaultFont($this->_defaultFont);
    }
    $final     = array();
    $offset    = ((ord($character) - 32) * $this->_height);
    $startLine = $this->_commentLines + 1 + $offset;
    $lines     = array_slice($this->_fontFile, $startLine, $this->_height);
    foreach($lines as $line)
    {
      $final[] = str_replace(
        ['@', $this->_hardblank, "\n"],
        ['', ' ', ''],
        $line
      );
    }
    return $final;
  }

  /**
   * @param $string string text to generate
   *
   * @return string output content with new lines
   */
  public function render($string)
  {
    $out        = "";
    $characters = str_split($string);
    $chars      = array();
    foreach($characters as $char)
    {
      $chars[] = $this->getCharacter($char);
    }
    for($line = 0; $line < $this->_height; $line++)
    {
      foreach($chars as $charLines)
      {
        $out .= $charLines[$line];
      }
      $out .= "\n";
    }
    return $out;
  }
}
