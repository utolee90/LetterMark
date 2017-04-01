<?php
/**
 * lettermark.php - Letter Mark Renderer
 * Copyright (C) 2015 koreapyj koreapyj0@gmail.com, Modified by Utolee90
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 */
 
class LetterMark {

    function __construct($wtext, $title) { 

        $this->list_tag = array( # Ordered Line Parser
            array('*', 'ul'),
            array('1.', 'ol'),
            array('A.', 'ol style="list-style-type:upper-alpha"'),
            array('a.', 'ol style="list-style-type:lower-alpha"'),
            array('I.', 'ol style="list-style-type:upper-roman"'),
            array('i.', 'ol style="list-style-type:lower-roman"')
        );
		
		
        $this->multi_bracket = array( // Render Process Glitch -> Disabled.
		    array( // Nowiki Tag.
                'open'	=> '/*',
                'close' => '*/',
                'multiline' => true,
                'processor' => array($this,'renderProcessor')),
            array(
                'open'	=> '{{{',
                'close' => '}}}',
                'multiline' => true,
                'processor' => array($this,'renderProcessor')),
			array(
                'open'	=> '<source',
                'close' => '</source>',
                'multiline' => true,
                'processor' => array($this,'renderProcessor')),
			array(
                'open'	=> '<syntaxhighlight',
                'close' => '</syntaxhighlight>',
                'multiline' => true,
                'processor' => array($this,'renderProcessor')),
            array(
                'open'	=> '<pre>',
                'close' => '</pre>',
                'multiline' => true,
                'processor' => array($this,'renderProcessor')),
       /*   array(  // Not processed
                'open'	=> '{{|',
                'close' => '|}}',
                'multiline' => true,
                'processor' => array($this,'renderProcessor')),  */
            array(
                'open'	=> '<nowiki>',
                'close' => '</nowiki>',
                'multiline' => true,
                'processor' => array($this,'renderProcessor')), 
 
        );  

        $this->single_bracket = array(
            array(
                'open'	=> '{{{',
                'close' => '}}}',
                'multiline' => false,
                'processor' => array($this,'textProcessor')),
            array(
                'open'	=> '[[',
                'close' => ']]',
                'multiline' => false,
                'processor' => array($this,'linkProcessor')),
            /* array( # ignore this parser
                'open'	=> '{{|',
                'close' => '|}}',
                'multiline' => false,
                'processor' => array($this,'textProcessor')), */
            array(
                'open'	=> '{{',
                'close' => '}}',
                'multiline' => false,
                'processor' => array($this,'mediawikiProcessor')),
            array(
                'open'	=> '[',
                'close' => ']',
                'multiline' => false,
                'processor' => array($this,'macroProcessor')),
            array(
                'open'	=> '~~',
                'close' => '~~',
                'multiline' => false,
                'processor' => array($this,'textProcessor')),
            array(
                'open'	=> '--',
                'close' => '--',
                'multiline' => false,
                'processor' => array($this,'textProcessor')),
            array(
                'open'	=> '__',
                'close' => '__',
                'multiline' => false,
                'processor' => array($this,'textProcessor')),
            array(
                'open'	=> '^^',
                'close' => '^^',
                'multiline' => false,
                'processor' => array($this,'textProcessor')),
            array(
                'open'	=> ',,',
                'close' => ',,',
                'multiline' => false,
                'processor' => array($this,'textProcessor')),
/*          array( # replaced by below code $$
                'open'	=> '$ ',
                'close' => ' $',
                'multiline' => false,
                'processor' => array($this,'textProcessor')), */
			array(
                'open'	=> '$$',
                'close' => '$$',
                'multiline' => false,
                'processor' => array($this,'textProcessor')),
			array(
                'open'	=> '<source',
                'close' => '</source>',
                'multiline' => false,
                'processor' => array($this,'textProcessor')),
			array(
                'open'	=> '<syntaxhighlight',
                'close' => '</syntaxhighlight>',
                'multiline' => false,
                'processor' => array($this,'textProcessor')),				
            array(
                'open'	=> '<!--',
                'close' => '-->',
                'multiline' => false,
                'processor' => array($this,'textProcessor')),
			array( # Alternative Comment Parser
                'open'	=> '/*',
                'close' => '*/',
                'multiline' => false,
                'processor' => array($this,'textProcessor')),
            array(
                'open'	=> '<nowiki>',
                'close' => '</nowiki>',
                'multiline' => false,
                'processor' => array($this,'textProcessor')),
	        array( # References used by DokuWiki
                'open'	=> '((',
                'close' => '))',
                'multiline' => false,
                'processor' => array($this,'textProcessor')),
			array( # File Parser or 
                'open'	=> '<<',
                'close' => '>>',
                'multiline' => false,
                'processor' => array($this,'textProcessor')),
			/*array( # Several Parser, Later Supported
                'open'	=> '&',
                'close' => ';',
                'multiline' => false,
                'processor' => array($this,'textProcessor')),  */
			 array( # Bolf Parser, deactivate after blank or  /[\t]*(\*|\#|:|;)
                'open'	=> '**',
                'close' => '**',
                'multiline' => false,
                'processor' => array($this,'astProcessor')),			
		     /* array( # Ping Tag - Needs more improvement.
                'open'	=> '{@',
                'close' => '}',
                'multiline' => false,
                'processor' => array($this,'textProcessor')), */
			/*array( # Special Page Tag - Parser Error occurs - Disabled.
                'open'	=> '[[[',
                'close' => ']]]',
                'multiline' => false,
                'processor' => array($this,'textProcessor')), */
        );

        $this->WikiPage = $wtext;
        $this->title = $title;

        $this->refnames = array();

        $this->toc = array();
        $this->fn = array();
        $this->fn_cnt = 0;
        $this->prefix = '';
    }

    public function toHtml() {
        $this->whtml = $this->WikiPage;
        $this->whtml = $this->htmlScan($this->whtml);
        return $this->whtml;
    }

    protected function htmlScan($text) { # Line Parser 
        $result = '';
        $len = strlen($text);
        $line = ''; // Start with Empty line

        for($i=0;$i<$len;self::nextChar($text,$i)) {
            $now = self::getChar($text,$i);
            if($line == '' && $now == ' ' && $list = $this->listParser($text, $i)) {
                $result .= ''
                    .$list
                    .'';
                $line = '';
                $now = '';
                continue;
            }
			


            if(self::startsWith($text, '|', $i) && $table = $this->tableParser($text, $i)) { // execute tableparser
                $result .= ''
                    .$table
                    .'';
                $line = '';
                $now = '';
                continue;
            }

            if($line == '' && self::startsWith($text, '>', $i) && $blockquote = $this->bqParser($text, $i)) { // blockquote 
                $result .= ''
                    .$blockquote
                    .'';
                $line = '';
                $now = '';
                continue;
            }
		
			

            foreach($this->multi_bracket as $bracket) { // activate multi_bracket Parser 
                if(self::startsWith($text, $bracket['open'], $i) && $innerstr = $this->bracketParser($text, $i, $bracket, false)) {
                    $result .= ''
                        .$this->lineParser($line, '')
                        .$innerstr
                        .'';
                    $line = '';
                    $now = '';
                    break;
                }
            }

            if($now == "\n") { // line parse ends - newline
                $result .= $this->lineParser($line, '');
                $line = '';
            }
            else
                $line.=$now;
        }
        if($line != '')
            $result .= $this->lineParser($line, 'notn');
        return $result;
    }

    protected function bqParser($text, &$offset) { #
        $len = strlen($text);
        $innerhtml = '';
        for($i=$offset;$i<$len;$i=self::seekEndOfLine($text, $i)+1) {
            $eol = self::seekEndOfLine($text, $i);
            if(!self::startsWith($text, '>', $i)) {
                // table end
                break;
            }
            $i+=1;
            $innerhtml .= '<p>' . $this->formatParser(substr($text, $i, $eol-$i)). "</p>";
        }
        if(empty($innerhtml))
            return false;

        $offset = $i-1;

        if(preg_match_all('/<p>(>*)?(.*?)<\/p>/', $innerhtml, $matches, PREG_SET_ORDER)) {
            $innerhtml = '';
            foreach($matches as $line => $match) {
                $match[2] = trim($match[2]);
                if(strlen($match[1]) == 0) {
                    $innerhtml .= $match[2] . "\n";
                } else {
                    if (isset($matches[$line - 1])) {
                        if (strlen($match[1]) > strlen($matches[$line - 1][1])) {
                            for ($n = 1; $n <= strlen($match[1]) - strlen($matches[$line - 1][1]); $n++) {
                                $innerhtml .= '<blockquote>' . "\n";
                            }
                            $innerhtml .= $match[2] . "\n";
                            if (isset($matches[$line + 1]) && strlen($match[1]) > strlen($matches[$line + 1][1])) {
                                for ($n = 1; $n <= strlen($match[1]) - strlen($matches[$line + 1][1]); $n++) {
                                    $innerhtml .= '</blockquote>' . "\n";
                                }
                            }
                        } elseif (strlen($match[1]) < strlen($matches[$line - 1][1])) {
                            for ($n = 1; $n <= strlen($matches[$line - 1][1]) - strlen($match[1]); $n++) {
                                $innerhtml .= '</blockquote>' . "\n";
                            }
                            $innerhtml .= $match[2] . "\n";
                        } elseif (strlen($match[1]) == strlen($matches[$line - 1][1])) {
                            $innerhtml .= '</br>' . $match[2] . "\n";
                        }
                    } elseif (!isset($matches[$line - 1])) {
                        for ($n = 1; $n <= strlen($match[1]); $n++) {
                            $innerhtml .= '<blockquote>' . "\n";
                        }
                        $innerhtml .= $match[2] . "\n";
                        if (isset($matches[$line + 1]) && strlen($match[1]) > strlen($matches[$line + 1][1])) {
                            for ($n = 1; $n <= strlen($match[1]) - strlen($matches[$line + 1][1]); $n++) {
                                $innerhtml .= '</blockquote>' . "\n";
                            }
                        }
                    }
                    if (!isset($matches[$line + 1])) {
                        for ($n = 1; $n <= strlen($match[1]); $n++) {
                            $innerhtml .= '</blockquote>' . "\n";
                        }
                    }
                }
            }

        }
        
        return '<blockquote>'.$innerhtml.'</blockquote>'."\n";
    }

    protected function linkProcessor($text, $type) { // deal with [[ ]]
        if(preg_match('/^(?:http|https|ftp|ftps)\:\/\/\S+/', $text, $ex_link)) {
            $ex_link = explode('|', $ex_link[0]);
            if(count($ex_link) - 1 != 0 && isset($ex_link[count($ex_link) - 1]))
                return '['.$ex_link[0].' '.$ex_link[count($ex_link) - 1].']';
            else
                return '['.$ex_link[0].']';
        }
        $text = preg_replace('/(https?.*?(\.jpeg|\.jpg|\.png|\.gif))/', '<img src="$1">', $text);
/*      if(preg_match('/(.*)\|(\[\[파일:.*)\]\]/', $text, $filelink)) // File Parser - Open as Image, Igonred
            return $filelink[2].'|link='.str_replace(' ', '_',$filelink[1]).']]'; 
*/
        if(preg_match('/^(파일:.*?(?!\.jpeg|\.jpg|\.png|\.gif))\|(.*)/i', $text, $namu_image)) { //ignore 파일:~ to avoid malfunction. Instead Use <<파일:>>
/*            $properties = explode("&", $namu_image[2]);

            foreach($properties as $n => $each_property) {
                if(preg_match('/^width=(.*)/i', $each_property, $width)) {
                    if(self::endsWith($width[1], '%'))
                        continue;
                    $imgwidth[1] = str_ireplace('px', '', $width[1]);
                    unset($properties[$n]);
                    continue;
                }

                if(preg_match('/^height=(.*)/i', $each_property, $height)) {
                    if(self::endsWith($height[1], '%'))
                        continue;
                    $imgheight[1] = str_ireplace('px', '', $height[1]);
                    unset($properties[$n]);
                    continue;
                }

                $properties[$n] = str_ireplace('align=', '', $each_property);
            }



            $property = '|';
            foreach($properties as $n => $each_property)
                $property .= $each_property.'|';

            if(isset($imgwidth) && isset($imgheight))
                $property .= $imgwidth[1] . 'x' . $imgheight[1] . 'px|';
            elseif(isset($imgwidth))
                $property .= $imgwidth[1].'px|';
            elseif(isset($imgheight))
                $property .= 'x'.$imgheight[1].'px|';

            $property = substr($property, 0, -1);

            return '[['.$namu_image[1].$property.']]'; */
        }
        return '[[' . $this->formatParser($text) . ']]';
    }

    protected function macroProcessor($text, $type) {
        $text = $this->formatParser($text);
        switch(strtolower($text)) { // remove input for Korean.
            case 'br':
                return '<br>';
            case 'date':
            case 'datetime':
			case '날짜':
                return date('Y-m-d H:i:s');
			case 'toc':
            case 'tableofcontents':
			case '목차':
                return '__TOC__';
            case 'footnote':
			case 'references':
			case '각주':
			case '주석':
                return '<references />';  
            default:
                if(self::startsWithi($text, 'include') && preg_match('/^include\((.+)\)$/i', $text, $include)) { // Include parser 
                    $include[1] = str_replace(',', '|', $include[1]);
                    $include[1] = urldecode($include[1]);
                    return '{{'.$include[1].'}}'."\n";
                }
                if(self::startsWithi($text, 'anchor') && preg_match('/^anchor\((.+)\)$/i', $text, $anchor)) //anchor parser
                    return '<div id="'.$anchor[1].'"></div>';
                if(self::startsWith($text, '*') && preg_match('/^\*([^ ]*)([ ].+)?$/', $text, $note)) { // reference parser
                    if(isset($note[1]) && isset($note[2]) && $note[1] !== '') {
                        foreach($this->refnames as $refname) {
                            if($refname === $note[1])
                                return '<ref name="'.$note[1].'" />';
                        }
                        array_push($this->refnames, $note[1]);
                        return '<ref name="' . $note[1] . '">' . $note[2] . '</ref>';
                    } elseif(isset($note[2]))
                        return '<ref>'.$note[2].'</ref>';
                    elseif(isset($note[1]))
                        return '<ref name="'.$note[1].'" />';
                }
/*               if(preg_match('/^(youtube|nicovideo)\((.*)\)$/i', $text, $video_code))  // embedvideo parser - disabled
*                    return $this->videoProcessor($video_code[2], strtolower($video_code[1])); 
*/

        }
        return '['.$text.']';
    }

/*     protected function videoProcessor($text, $service) { // Videoprocessor - disabled
*        $text = str_replace('|', ',', $text);
        $options = explode(",", $text);
        $text = '';

        foreach($options as $key => $value) {
            if($key == 0) {
                $service = str_replace('nicovideo', 'nico', $service);
                $text .= '{{#evt:service='.$service.'|id='.$value;
                continue;
            }

            $option = explode("=", $value);
            if($option[0] == 'width') {
                $width = $option[1];
                continue;
            } elseif ($option[0] == 'height') {
                $height = $option[1];
                continue;
            } elseif (preg_match('/(\d+)x(\d+)/', $value, $match)) {
                $width = $match[1];
                $height = $match[2];
                continue;
            }

            $text .= '|'.$value;
        }

        if(isset($width) && isset($height))
            $text .= '|dimensions='.$width.'x'.$height;
        elseif(isset($width))
            $text .= '|dimensions='.$width;
        elseif(isset($height))
            $text .= '|dimensions=x'.$height;

        return $text.'}}';

    }
*/
    protected function mediawikiProcessor($text, $type) { // template parser - using mediawiki parser
        if($type == '{{')
            return '{{'.$text.'}}';
    }


    protected function lineParser($line, $type) { // line parser
		if($line == '----')
			return '<hr>';

		$line = $this->blockParser($line);

		if($type == 'notn')
			return $line;
		else
            return $line."\n";
	}

	protected function formatParser($line) { // Parsing each line - increasing line
	    $astparse = true; //Line parsing default 
		$line_len = strlen($line);

		for($j=0;$j<$line_len;$cr=self::nextChar($line,$j)) {
			if ($j==1 && ($cr == ' ' || $cr == '#' || $cr == '*' || $cr == ':' || $cr == ';' || $cr =='' ))
				$astparse=false; // 첫 문단이 문단기호일 때에는 강제로 끔.
			foreach($this->single_bracket as $bracket) {
				$nj=$j;
				if(self::startsWith($line, $bracket['open'], $j) && $innerstr = $this->bracketParser($line, $nj, $bracket, $astparse)) {
					$line = substr($line, 0, $j).$innerstr.substr($line, $nj+1);
					$line_len = strlen($line);
					$j+=strlen($innerstr)-1;
					break;
				}
			}
		}
		return $line;
	}

	protected function bracketParser($text, &$now, $bracket, $astparse) { // BracketParser - parsing function between brackets - add variable $astparser 
		$len = strlen($text);
		$cnt = 0;
		$done = false;

		$openlen = strlen($bracket['open']);
		$closelen = strlen($bracket['close']);

		for($i=$now;$i<$len;self::nextChar($text,$i)) {
			if(self::startsWith($text, $bracket['open'], $i) && !($bracket['open']==$bracket['close'] && $cnt>0)) {
				$cnt++;
				$done = true;
				$i+=$openlen-1; // 
			}elseif(self::startsWith($text, $bracket['close'], $i)) {
				$cnt--;
				$i+=$closelen-1;
			}elseif(!$bracket['multiline'] && $text[$i] == "\n")
				return false;

			if($cnt == 0 && $done) { // parsing when closed. 
				$innerstr = substr($text, $now+$openlen, $i-$now-($openlen+$closelen)+1);

				if((!strlen($innerstr)) ||($bracket['multiline'] && strpos($innerstr, "\n")===false))
					return false;
				elseif ($bracket['open'] == '**')
					$result = self::astProcessor($innerstr,$astparse); 
				else
					$result = call_user_func_array($bracket['processor'],array($innerstr, $bracket['open']));
				$now = $i;
				return $result;
			}
		}
		return false;
	}
	
	protected static function getChar($string, $pointer){
		if(!isset($string[$pointer])) return false;
		$char = ord($string[$pointer]);
		if($char < 128){
			return $string[$pointer];
		}else{
			if($char < 224){
				$bytes = 2;
			}elseif($char < 240){
				$bytes = 3;
			}elseif($char < 248){
				$bytes = 4;
			}elseif($char == 252){
				$bytes = 5;
			}else{
				$bytes = 6;
			}
			$str = substr($string, $pointer, $bytes);
			return $str;
		}
	}

	protected static function nextChar($string, &$pointer){
		if(!isset($string[$pointer])) return false;
		$char = ord($string[$pointer]);
		if($char < 128){
			return $string[$pointer++];
		}else{
			if($char < 224){
				$bytes = 2;
			}elseif($char < 240){
				$bytes = 3;
			}elseif($char < 248){
				$bytes = 4;
			}elseif($char == 252){
				$bytes = 5;
			}else{
				$bytes = 6;
			}
			$str = substr($string, $pointer, $bytes);
			$pointer += $bytes;
			return $str;
		}
	}

	protected static function startsWith($haystack, $needle, $offset = 0) { // Check initial characters
		$len = strlen($needle);
		if(($offset+$len)>strlen($haystack))
			return false;
		return $needle == substr($haystack, $offset, $len);
	}

	protected static function startsWithi($haystack, $needle, $offset = 0) { // Check initial characters
		$len = strlen($needle);
		if(($offset+$len)>strlen($haystack))
			return false;
		return strtolower($needle) == strtolower(substr($haystack, $offset, $len));
	}

	protected static function endsWith($haystack, $needle) { // Check n terminal characters.
		// search forward starting from end minus needle length characters
		return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
	}

	
	protected static function seekEndOfLine($text, $offset=0) {
		return ($r=strpos($text, "\n", $offset))===false?strlen($text):$r;
	}
	
	protected function tableParser($text, &$offset) { #table Parser
		$len = strlen($text);

		$tableInnerStr = '';
		$tableStyleList = array();
        $caption = '';
        for($i=$offset;$i<$len;$i=self::seekEndOfLine($text, $i)+1) {
			$now = self::getChar($text,$i);
			$eol = self::seekEndOfLine($text, $i);
			if(!self::startsWith($text, '||', $i)) {
				// table end
                break;
			}
			$line = substr($text, $i, $eol-$i);
			$td = explode('||', $line);
			$td_cnt = count($td);

			$trInnerStr = '';
			$simpleColspan = 0;
			for($j=1;$j<$td_cnt-1;$j++) {
				$innerstr = htmlspecialchars_decode($td[$j]);

				if($innerstr=='') {
					$simpleColspan += 1;
					continue;
				} elseif(preg_match('/^\|.*?\|/', $innerstr)) {
                    $caption_r = explode('|', $innerstr);
                    $caption = '<caption>'.$caption_r[1].'</caption>';
                    $innerstr = $caption_r[2];
                }

				$tdAttr = $tdStyleList = array();
				$trAttr = $trStyleList = array();
				
				if($simpleColspan != 0) {
					$tdAttr['colspan'] = $simpleColspan+1;
					$simpleColspan = 0;
				}
				


				while(self::startsWith($innerstr, '<') && !preg_match('/^<[^<]*?>([^<]*?)<\/.*?>/', $innerstr) && !self::startsWithi($innerstr, '<br')) { // Cell Properties
					$dummy=0;
					$prop = $this->bracketParser($innerstr, $dummy, array('open'	=> '<', 'close' => '>','multiline' => false,'processor' => function($str) { return $str; }), false); // always activate astParser
                    $prop = preg_replace('/^table([^ ])/', 'table $1', $prop);
                    $innerstr = substr($innerstr, $dummy+1);

                    switch($prop) {
						case '(':
							break;
						case ':':
							$tdStyleList['text-align'] = 'center';
							break;
						case ')':
							$tdStyleList['text-align'] = 'right';
							break;
						default:
							if(self::startsWith($prop, 'table ')) {
								$tbprops = explode(' ', $prop);
								foreach($tbprops as $tbprop) {
									if(!preg_match('/^([^=]+)=(?|"(.*)"|\'(.*)\'|(.*))$/', $tbprop, $tbprop))
										continue;
									switch($tbprop[1]) {
										case 'align':
											switch($tbprop[2]) {
												case 'center':
													$tableStyleList['margin-left'] = 'auto';
													$tableStyleList['margin-right'] = 'auto';
													break;
												case 'right':
													$tableStyleList['float'] = 'right';
													$tableStyleList['margin-left'] = '10px';
													break;
											}
											break;
										case 'bgcolor':
											$tableStyleList['background-color'] = $tbprop[2];
											break;
										case 'bordercolor':
											$tableStyleList['border'] = '2px solid ';
											$tableStyleList['border'] .= $tbprop[2];
											break;
										case 'width':
                                            if(is_numeric($tbprop[2]))
                                                $tbprop[2] .= 'px';
											$tableStyleList['width'] = $tbprop[2];
											break;
										case 'caption':
											$caption = '<caption>'.$tbprop[2].'</caption>';
									}
								}
							}
							elseif(preg_match('/^(\||\-|v|\^)\|?([0-9]+)$/', $prop, $span)) {
								if($span[1] == '-') {
									$tdAttr['colspan'] = $span[2];
									break;
								}
								elseif($span[1] == '|') {
									$tdAttr['rowspan'] = $span[2];
									break;
								}
								elseif($span[1] == '^') {
									$tdAttr['rowspan'] = $span[2];
									$tdStyleList['vertical-align'] = 'top';
									break;
								}
								elseif($span[1] == 'v') {
									$tdAttr['rowspan'] = $span[2];
									$tdStyleList['vertical-align'] = 'bottom';
									break;
								}
							}
							/* elseif(preg_match('/^#(?:([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})|([A-Za-z]+))$/', $prop, $span)) { // background color tag - disable in order to avoid collision
								$tdStyleList['background-color'] = $span[1]?'#'.$span[1]:$span[2];
								break;
							} */
							elseif(preg_match('/^([^=]+)=(?|"(.*)"|\'(.*)\'|(.*))$/', $prop, $match)) {
								switch($match[1]) {
									case 'bgcolor':
										$tdStyleList['background-color'] = $match[2];
										break;
									case 'rowbgcolor':
										$trStyleList['background-color'] = $match[2];
                                        break;
									case 'width':
										$tdStyleList['width'] = $match[2];
										break;
									case 'height':
										$tdStyleList['height'] = $match[2];
										break;
								}
							}
                            else
                                $tdStyleList['background-color'] = $prop;
					}
				}

                if(empty($tdStyleList['text-align'])) { /*  //Ignore align parser by setting whitespace adjacent to division parser.
                    if(self::startsWith($innerstr, ' ') && self::endsWith($innerstr, ' '))
                        $tdStyleList['text-align'] = 'center';
                    elseif(self::startsWith($innerstr, ' ') && !self::endsWith($innerstr, ' '))
                        $tdStyleList['text-align'] = null;
                    elseif(!self::startsWith($innerstr, ' ') && self::endsWith($innerstr, ' '))
                        $tdStyleList['text-align'] = 'right';
                    else */
                        $tdStyleList['text-align'] = null; 
                }

                $innerstr = trim($innerstr);

				$tdAttr['style'] = '';
				foreach($tdStyleList as $styleName =>$tdstyleValue) {
					if(empty($tdstyleValue))
						continue;
					$tdAttr['style'] .= $styleName.': '.$tdstyleValue.'; ';
				}
				
				$trAttr['style'] = '';
				foreach($trStyleList as $styleName =>$trstyleValue) {
					if(empty($trstyleValue))
						continue;
					$trAttr['style'] .= $styleName.': '.$trstyleValue.'; ';
				}

				$tdAttrStr = '';
				foreach($tdAttr as $propName => $propValue) {
					if(empty($propValue))
						continue;
					$tdAttrStr .= ' '.$propName.'="'.str_replace('"', '\\"', $propValue).'"';
				}
				
				if (!isset($trAttrStri)) {
					$trAttrStri = true;
					$trAttrStr = '';
					foreach($trAttr as $propName => $propValue) {
						if(empty($propValue))
							continue;
						$trAttrStr .= ' '.$propName.'="'.str_replace('"', '\\"', $propValue).'"';
					}
				}
				$trInnerStr .= '<td'.$tdAttrStr.'>'.$this->blockParser($innerstr).'</td>';
			}
			$tableInnerStr .= !empty($trInnerStr)?'<tr'.$trAttrStr.'>'.$trInnerStr.'</tr>':'';
			unset($trAttrStri);
		}

		if(empty($tableInnerStr))
			return false;

		$tableStyleStr = '';
		foreach($tableStyleList as $styleName =>$styleValue) {
			if(empty($styleValue))
				continue;
			$tableStyleStr .= $styleName.': '.$styleValue.'; ';
		}

		$tableAttrStr = ($tableStyleStr?' style="'.$tableStyleStr.'"':'');
		$result = '<table class="wikitable"'.$tableAttrStr.'>'.$caption.$tableInnerStr."</table>\n";
		$offset = $i-1;
		return $result;
	}

    protected function textProcessor($otext, $type) {
        if( $type != '<source' && $type != '<syntaxhighlight' && $type != '<nowiki>' && $type !='/*') # error prevent
            $text = $this->formatParser($otext);
        else
            $text = $otext;
        switch($type) {
            case '--': #strike effect
            case '~~':
                if(!self::startsWith($text, 'item-') && !self::endsWith($text, 'UNIQ') && !self::startsWith($text, 'QINU') && !preg_match('/^.*?-.*-QINU/', $text) && !self::startsWith($text, 'h-'))
                    return '<s>'.$text.'</s>'; // Strikethrough
                else
                    return $type.$text.$type;
            case '__':
			     $mw_magic_words= array('TOC', 'NOTOC', 'FORCETOC', 'NOEDITSECTION', 'NEWSECTIONLINK', 'NONEWSECTIONLINK', 'NOGALLARY', 'HIDDENCAT', 'NOCONTENTCONVERT', 'NOCC', 'NOTITLECONVERT', 'NOTC', 'INDEX', 'NOINDEX', 'STATICREDIRECT' );
				 #set mediawiki magic words.

                if(in_array($text, $mw_magic_words) || preg_match('/^.*?(\.jpeg|\.jpg|\.png|\.gif)/', $text)) # Keep parsing Magic Words starting "__"
                    return $type.$text.$type;
                else
                    return '<u>'.$text.'</u>';
            case '^^':
                return '<sup>'.$text.'</sup>';
            case ',,':
			    if (!self::startsWith($text, ' ') && !self::startsWith($text, '\t'))
                    return '<sub>'.$text.'</sub>';
				else
					return ',,'.$text.',,';
            case '<!--':
			case '/*': # Alternative Comment Parser
                return '<!--'.$text.'-->';
            #case '{{|': # text box syntax : deprecated in LetterMark.
             #   return '<poem style="border: 2px solid #d6d2c5; background-color: #f9f4e6; padding: 1em;">'.$text.'</poem>';
            case '<nowiki>':
                return '<nowiki>'.$text.'</nowiki>';
            case '{{{':
                if(self::startsWith($text, '#!html')) { #html tag.
                   # $html = substr($text, 6);
                   # $html = htmlspecialchars_decode($html);
			      return /*'<html>'.$html.'</html>'; */ $type.$text.'}}}';
                } elseif(self::startsWithi($text, '#!wiki') && preg_match('/([^\n]*)\n(((((.*)(\n)?)+)))/', substr($text, 7), $match)) { // Wiki Parser
                return '<div '.$match[1].'>'.$match[2].'</div>';
                } elseif(self::startsWithi($text, '#!syntax') && preg_match('/#!syntax ([^\s]*)/', $text, $match)) { # #syntax Tag -> Activagte Syntaxhighlight tag
                    return '<syntaxhighlight lang="'.$match[1].'" line="1">'.preg_replace('/#!syntax ([^\s]*)/', '', $text).'</syntaxhighlight>';
                } elseif(self::startsWithi($text, '#!align') && preg_match('/#!align ([^\s]*)/', $text, $match)) {				// align symbol
                    return '<div style="text-align:' . $match[1] . ';">' . preg_replace('/#!align ([^\s]*)/', '', $text) . '</div>';
			    } elseif(self::startsWithi($text, '#!font')) {				// font symbol
			       $check=explode(';', $text, 2 ); // Explode by ';'
                    return '<div style="font-family:' . substr($check[0],7) . ';">' . $check[1] . '</div>';
			    }elseif(self::startsWithi($text, '#!math')) {				// math symbol
                    return '<math>' . substr($text, 7) . '</math>';
			    } elseif(preg_match('/^#(?:([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})|([A-Za-z]+) ) (.*)$/', $text, $color)) { # Color tag #XXX, #XXXXXX or #color name
                    if(empty($color[1]) && empty($color[2]))
                        return $text;
                    return '<span style="color: '.(empty($color[1])?$color[2]:'#'.$color[1]).'">'.$this->formatParser($color[3]).'</span>';
                } elseif(preg_match('/^\+([1-9]) (.*)$/', $text, $size)) { # Larger Size Tag
				    
                        for ($i=1; $i<=$size[1]; $i++){
                           if(isset($big_before) && isset($big_after)) { 
                               $big_before .= '<big>';
                               $big_after .= '</big>';
                            } else {
                               $big_before = '<big>';
                               $big_after = '</big>';
                            }
                       }
                        return $big_before.$this->formatParser($size[2]).$big_after;
					
                } elseif(preg_match('/^\-([1-5]) (.*)$/', $text, $size)) { #Smaller Size Tag
                    for ($i=1; $i<=$size[1]; $i++){
                        if(isset($small_before) && isset($small_after)) {
                            $small_before .= '<small>';
                            $small_after .= '</small>';
                        } else {
                            $small_before = '<small>';
                            $small_after = '</small>';
                        }
                    }

                    return $small_before.$this->formatParser($size[2]).$small_after;
                }
				  elseif(self::startsWithi($text, 'n ')||self::startsWithi($text, 'N ')||self::startsWithi($text, 'x') || self::startsWithi($text, 'X')) { #nowiki tag
				     if(self::endsWith($text, ' x') || self::endsWith($text, ' n') ) // terminator tag.
					    return '<nowiki>'.substr($text, 2, -2).'</nowiki>';
					 else 	
				        return '<nowiki>' . substr($text, 2). '</nowiki>';
				  }
				  elseif((self::startsWithi($text, 'f ')||self::startsWithi($text, 'F '))&& preg_match('/^(f|F) ([^\s]*)/', $text, $match) ){ #font face tag
				        return '<span style="font-family:'.$match[1].'">'.preg_replace('/(f|F) ([^\s]*)/', '', $text).'</span>';					
				}
				else {

			return /*'<nowiki>' . $text . '</nowiki>'; */ '{{{'.$text.'}}}'; #Ignore Nowiki 
                }
			case '((': #footnotes of dokuwiki
			   if(self::startsWith($text, ' ') && !self::endsWith($text, 'UNIQ') && !preg_match('/^.*?-.*-QINU/', $text) && (strlen(substr($text,1))>0) ) {
                  return '<ref>'.$text.'</ref>';
               } else { # Note that
			       $ref= explode("|", $text, 2); // Using pipe instead of space
                   if(isset($ref[0]) && isset($ref[1])) {
                        return '<ref name="' . $ref[0] . '">' . $ref[1] . '</ref>';
                    }
					else{ // No space in front of or behind pipe 
						if (self::startsWith($text, '|'))
							return '<ref>'.substr($text, 1).'</ref>';
						elseif (self::endsWith($text, '|'))
						    return '<ref name="'.substr($text, -1).'"></ref>"';
						else
							return '(('.$text.'))'; //No whitespace exists between (( & ))  -> (()) to avoid error.
						}
			   } 	
			case '$$': #Replace Math Tag
                if(!self::startsWith($text, 'h-'))
                    return '<math>'.$text.'</math>'; #
                else
                    return $type.$text.$type; 
			case '<<': #File Parser Tag as Namumark/Mediawiki
                if(preg_match('/^.*?(\.jpeg|\.jpg|\.png|\.gif|\.svg|\.apng|\.bmp|\.tiff?|\.avi|\.mp3|\.mp4|\.ogg|\.oga|\.ogv|\.wma|\.wmv).*/i', $text)){ // File Parser. Format name appears.
				    if(preg_match('/^(moni|namu|enha|veda|그림|나무|베다|파일|이미지|미디어):.*?(?!\.jpeg|\.jpg|\.png|\.gif)\|(.*)/i', $text, $namu_image)) { // Namumark Parser if starts with Korean.
                        $properties = explode("&", $namu_image[2]);

                         foreach($properties as $n => $each_property) {
                         if(preg_match('/^width=(.*)/i', $each_property, $width)) {
                             if(self::endsWith($width[1], '%'))
                             continue;
                             $imgwidth[1] = str_ireplace('px', '', $width[1]);
                             unset($properties[$n]);
                             continue;
                             }

                         if(preg_match('/^height=(.*)/i', $each_property, $height)) {
                             if(self::endsWith($height[1], '%'))
                             continue;
                             $imgheight[1] = str_ireplace('px', '', $height[1]);
                             unset($properties[$n]);
                             continue;
                        }

                        $properties[$n] = str_ireplace('align=', '', $each_property);
                           }

                        $property = '|';
                        foreach($properties as $n => $each_property)
                             $property .= $each_property.'|';

                         if(isset($imgwidth) && isset($imgheight))
                              $property .= $imgwidth[1] . 'x' . $imgheight[1] . 'px|';
                         elseif(isset($imgwidth))
                              $property .= $imgwidth[1].'px|';
                         elseif(isset($imgheight))
                               $property .= 'x'.$imgheight[1].'px|';

                         $property = substr($property, 0, -1);

                         return '[['.$namu_image[1].$property.']]';
                    }						 
				    elseif (preg_match('/^(A|Audio|AV|Background|Display|F|File|Gallary|I|Image|M|Media|Mediawiki|MW|Play|Show|Sound|Video)\:(.*)/i', $text, $text_parse)){ // Remove certain prefixes.
						return '[[File:'.$text_parse.']]'; # Remaining case - Parsing codes as that of Mediawiki.
					}
					else
						return '[[File:'.$text.']]';
						
					
                }
				else{ // describe 《 or 『
					if(!self::startsWith($text, ' ') && !self::endsWith($text, ' ') && !self::startsWith($text, '\t') && !self::endsWith($text, '\t') ){ // No whitespace
						if (self::startsWith($text, '^') && self::endsWith($text, '^'))
							return '<span class="unicode">&#x300E;</span>'.substr($text, 1, -1).'<span class="unicode">&#x300F;</span>';
						else
							return '<span class="unicode">&#x300A;</span>'.$text.'<span class="unicode">&#x300B;</span>';
							
					}
					else
						return $type.$text.'>>'; 
				}
			/* case '&': // Special character parser - featured later
                return self::characterProcessor($type); */
			
			case '**': // Asteroid Processor, only activated by astProcessor
			if (self::startsWith($text, ' ') || self::startsWith($text, '\t'))
				return '**'.$text.'**'; // Prevent Parsing error.
			else
			    return '<b>'.$text.'</b>'; 
				
			/* case '{@': #Reply Tag - will be implemented future.
                if(!self:startsWith($text, ' ')){
                    $user_list= preg_split("/(,|;)/", $text);					
					for ($j=1;$j<=count($user_list);$j++){
						if($j===1)
							return '@[[:User:'.$user_list[$j-1].'|'.$user_list[$j-1].']]';
						else
							return ',[[:User:'.$user_list[$j-1].'|'.$user_list[$j-1].']]';
					}
                }else
			         return $type.$text.'}'; */		
            default:
                return $type.$text.$type;
        }
    }
	
	 protected function astProcessor($text, $astparse) { // deal with the parser ** -> Avoid collision with unordered list tag
	    if ($astparse == 'true')
			return self::textProcessor($text, '**');
		else
			return '**'.$text.'**';
		
	}
	
    protected function listParser($text, &$offset) {
        $listTable = array();
        $len = strlen($text);
        $lineStart = $offset;

        $quit = false;
        for($i=$offset;$i<$len;$before=self::nextChar($text,$i)) {
            $now = self::getChar($text,$i);
            if($now != ' ') {
                if($lineStart == $i) {
                    // list end
                    break;
                }

                $match = false;

                foreach($this->list_tag as $list_tag) { // Ordered List Tag
                    if(self::startsWith($text, $list_tag[0], $i)) {

                        if(!empty($listTable[0]) && $listTable[0]['tag']=='indent') {
                            $i = $lineStart;
                            $quit = true;
                            break;
                        }

                        $eol = self::seekEndOfLine($text, $lineStart);
                        $tlen = strlen($list_tag[0]);
                        $innerstr = substr($text, $i+$tlen, $eol-($i+$tlen));
                        $this->listInsert($listTable, $innerstr, ($i-$lineStart), $list_tag[1]);
                        $i = $eol;
                        $now = "\n";
                        $match = true;
                        break;
                    }
                }
                if($quit)
                    break;

                if(!$match) {
                    // indent
                    if(!empty($listTable[0]) && $listTable[0]['tag']!='indent') {
                        $i = $lineStart;
                        break;
                    }

                    $eol = self::seekEndOfLine($text, $lineStart);
                    $innerstr = substr($text, $i, $eol-$i);
                    $this->listInsert($listTable, $innerstr, ($i-$lineStart), 'indent');
                    $i = $eol;
                    $now = "\n";
                }
            }
            if($now == "\n") {
                $lineStart = $i+1;
            }
        }
        if(!empty($listTable[0])) {
            $offset = $i-1;
            return $this->listDraw($listTable);
        }
        return false;
    }

    private function listInsert(&$arr, $text, $level, $tag) {
        if(preg_match('/^#([1-9][0-9]*) /', $text, $start))
            $start = $start[1];
        else
            $start = 1;
        if(empty($arr[0])) {
            $arr[0] = array('text' => $text, 'start' => $start, 'level' => $level, 'tag' => $tag, 'childNodes' => array());
            return true;
        }

        $last = count($arr)-1;
        $readableId = $last+1;
        if($arr[0]['level'] >= $level) {
            $arr[] = array('text' => $text, 'start' => $start, 'level' => $level, 'tag' => $tag, 'childNodes' => array());
            return true;
        }

        return $this->listInsert($arr[$last]['childNodes'], $text, $level, $tag);
    }

    private function listDraw($arr) {
        if(empty($arr[0]))
            return '';

        $tag = $arr[0]['tag'];
        $start = $arr[0]['start'];
        $result = ($tag=='indent'?'':'<'.$tag.($start!=1?' start="'.$start.'"':'').'>');
        foreach($arr as $li) {
            $text = $this->blockParser($li['text']).$this->listDraw($li['childNodes']);
            $result .= $tag=='indent'?$text:'<li>'.$text.'</li>';
        }
        $result .= ($tag=='indent'?'':'</'.$tag.'>');
        $result .= "\n";
        return $result;
    }

    protected function blockParser($block) {
        return $this->formatParser($block);
    }

    protected function renderProcessor($text, $type) {

		if ($type=='/*') { // comment Parser.
			return '<!--'.$text.'-->'; 
		}
			

    }
	/* protected function characterProcessor($text) { // deal with the parser () 
	    switch($text){
			case '->': // Arrow symbol
			    return '→';
			case '<-':
			    return '←';
			case '^|':
			    return '↑';
			case '|v':
			    return '↓';
			case '<->':
			    return '↔';
			case '^|v':
			    return '↕';
			case '<=':
			    return '<span style="unicode">&#x21D0;</span>';
			case '=>':
			    return '<span style="unicode">&#x21D2;</span>;';
			case '^||':
			    return '<span style="unicode">&#x21D1;</span>;';
			case '||v':
			    return '<span style="unicode">&#x21D3;</span>;';
			case '<=>':
			    return '<span style="unicode">&#x21D4;</span>';
			case '^||v':
			    return '<span style="unicode">&#x21D5;</span>';
			case '^c': // copyright
			case '^C':
			case 'copyright':
			    return '<span style="unicode">&#169;</span>';
			case '^r': // Registered 
			case '^R':
			case 'registered':
			    return '<span style="unicode">&#174;</span>';
			case 'TM':
			case '^tm': // Trademark
			case '^TM':
			case 'trademark':
			    return '<span style="unicode">&#x2122;</span>';
			case 'ss': // Section
			case 'section':
			    return '<span style="unicode">&#167;</span>';
			case '+d': // dagger
			case 'dagger':
			    return '<span style="unicode">&#x2020;</span>';
			case '++d': // double dagger
			case 'ddagger':
			    return '<span style="unicode">&#x2021;</span>';
			case 'pilcrow': // Pilcrow mark
			case 'ql':
			case 'q|':
			    return '&#182;';
			case '<|': // triangle symbol
			case 'ltriangle':
			    return '&#x25C1;';
			case '|>':
			case 'rtriangle':
			    return '&#x25B7;';
			case '^_':
			case 'utriangle':
			    return '&#x25B3;';
			case '-v':
			case 'dtriangle':
			    return '&#x25BD;';
			case 'bltriangle':
			    return '&#x25C0;';
			case 'brtriangle':
			    return '&#x25B6;';
			case 'butriangle':
			    return '&#x25B2;';
			case 'bdtriangle':
			    return '&#x25BC;';
			case 'deg': // degree symbol
			    return '&#176;';
			case 'circ': // circle
			    return '&#x252f;';
			case 'bcirc':
			    return '&#x2b24;';
			case 'ccirc':
			    return '&#x25CB;';
			case 'bccirc':
			    return '&#x25cf;';
			case 'bstar':
			    return '&#x2605;';
			case 'wstar':
			case 'star':
			    return '&#x2606;';
			case '$00': // Circled numbers
			    return '&#x24EA;';
			case '$01':
			    return '&#x2460;';
			case '$02':
			    return '&#x2461;';
			case '$03':
			    return '&#x2462;';
			case '$04':
			    return '&#x2463;';
			case '$05':
			    return '&#x2464;';
			case '$06':
			    return '&#x2465;';
			case '$07':
			    return '&#x2466;';
			case '$08':
			    return '&#x2467;';
			case '$09':
			    return '&#x2467;';
			case '$nm': // Unit symbols
			    return '&#x339A;';
			case '$mum':
			case '$mim':
			    return '&#x339B;';
			case '$mm':
			    return '&#x339C;';
			case '$cm':
			    return '&#x339D;';
			case '$km':
			    return '&#x339E;';
			case '$in':
			    return '&#x33cc;';
			case '$mm2':
			    return '&#x339f;';
			case '$cm2':
			    return '&#x33A0;';
			case '$m2':
			    return '&#x33A1;';
			case '$km2':
			    return '&#x33A2;';
			case '$mm3':
			    return '&#x33a3;';
			case '$cm3':
			    return '&#x33A4;';
			case '$m3':
			    return '&#x33A5;';
			case '$km3':
			    return '&#x33A6;';
			case '$mul': // microliter, in tis case avoid collision with mil.
			    return '&#x3395;';
			case '$ml':
			    return '&#x3396;';
			case '$dl':
			    return '&#x3397;';
			case '$kl':
			    return '&#x3398;';
			case '$cc':
			    return '&#x33C4;';
			case '$ps':
			    return '&#x33B0;';
			case '$ns':
			    return '&#x33B1;';
			case '$mis':
			case '$mus':
			    return '&#x33b2;';
			case '$ms':
			    return '&#x33B3;';
			case '$mug':
			case '$mig':
			    return '&#x338D;';
			case '$mg':
			    return '&#x338E;';
			case '$kg':
			    return '&#x338F;';
			case '$KB':
			    return '&#x3385;';
			case '$MB':
			case '$MeB':
			    return '&#x3386;';
			case '$GB':
			    return '&#x3387;';
			case '$Hz':
			    return '&#x3390;';
			case '$kHz':
			    return '&#x3391;';
			case '$MHz':
			case '$MeHz':
			    return '&#x3392;';
			case '$THz':
			    return '&#x3393;';
			case '$pV':
			    return '&#x33B4;';
            case '$nV':
			    return '&#x33B5;';	
            case '$miV':
			case '$muV':
			    return '&#x33B6;';
            case '$mV':
                return '&#x33B7;';
            case '$kV':
                return '&#x33B8;';
            case '$MeV': // Avoid collision with mV
                return '&#x33B9;';
            case '$pW':
                return '&#x33BA;';
            case '$pW':
                return '&#x33BA;';
            case '$nW':
                return '&#x33BB;';
            case '$miW':
			case '$muW':
                return '&#x33BC;';
            case '$mW':
                return '&#x33BD;';
            case '$kW':
                return '&#x33BE;';
            case '$MeW': // Avoid Collision with mW
                return '&#x33BF;';
			case '$kohm':
            case '$kOhm':
                return '&#x33C0;';
            case '$Mohm':
            case '$Meohm':
            case '$MOhm':
            case '$MeOhm':
                return '&#x33C1;';
            case '$pA':
                return '&#x3380;';
            case '$nA':
                return '&#x3381;';
            case '$muA':
			case '$miA':
                return '&#x3382;';
            case '$mA':
                return '&#x3383;';
            case '$kA':
                return '&#x3384;';
            case '$mps':
			case '$m/s':
                return '&#x33A7;';
            case '$mps2':
			case '$m/s2':
                return '&#x33A8;';
            case '$rad':
                return '&#x33AD;';
            case '$radps':
			case '$rad/s':
                return '&#x33AE;';
            case '$radps2':
			case '$rad/s2':
			    return '&#x33AF;';
			case '$Pa':
			    return '&#x33A9;';
			case '$kPa':
			    return '&#x33AA;';
			case '$MPa':
			    return '&#x33AB;';
			case '$GPa':
			    return '&#x33AC;';
			case '$cal':
			    return '&#x3388;';
			case '$kcal':
			    return '&#x3389;';
			case '$dm':
			    return '&#x3377;';
			case '$dm2':
			    return '&#x3378;';
			case '$dm3':
			    return '&#x3379;';
			case '$fm':
			    return '&#x3399;';
			case '$hPa':
			    return '&#x3371;';
			case '$da':
			    return '&#x3372;';
			case '$AU':
			    return '&#x3373;';
			case '$bar':
			    return '&#x3374;';
			case '$oV':
			    return '&#x3375;';
			case '$pc':
			    return '&#x3376;';
			case '$IU':
			    return '&#x3377;';
			case '$pF':
			    return '&#x338A;';
			case '$nF':
			    return '&#x338B;';
			case '$miF':
			case '$muF':
			    return '&#x338C;';
			case '$Bq':
			    return '&#x33C3;';
			case '$cd':
			    return '&#x33C5;';
			case '$cpkg':
			case '$c/kg':
			    return '&#x33C6;';
			case '$Co.':
			    return '&#x33C7;';
			case '$dB':
			    return '&#x33C8;';
			case '$Gy':
			    return '&#x33C9;';
			case '$ha':
			    return '&#x33CA;';
			case '$kt':
			    return '$#x33CF;';
			case '$lm':
			    return '&#x33D0;';
			case '$ln':
			    return '&#x33D1;';
			case '$log':
			    return '&#x33D2;';
			case '$mbar': // avoid collision with Megabyte
			    return '&#x33D4;';
			case '$mil': // in this case return mil.
			    return '&#x33D5;';
			case '$mol':
			    return '&#x33D6;';
			case '$pH':
			    return '&#x33D7;';
			case '$PR':
			    return '&#x33DA;';
			case '$sr':
			    return '&#x33DB;';
			case '$Sv':
			    return '&#x33DC;';
			case '$Wb':
			    return '&#x33DD;';
			case '$Vpm':
			case '$V/m':
			    return '&#x33DE;';
			case '$A/m':
			case '$Apm':
			    return '&#x33DF;';
			case '$gal':
			    return '&#x33FF;';
			case '$am':
			    return '&#x33C2;';
			case '$pm':
			    return '&#x33D8;';
			case '$ppm':
			    return '&#x33D9;';
                			
			default:
			    return '&'.$text.';';
				
					
			
			
		}
	} */

}

