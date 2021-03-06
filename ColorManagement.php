<?php

class ColorManagement {
	
	/**
	 * @var
	 */
	private $hex;
	/**
	 * @var
	 */
	private $rgb;
	/**
	 * @var
	 */
	private $hsl;
	
	/**
	 * @param $hex string
	 */
	public function setHex( $hex ) {
		$this->hex = $hex;
	}
	
	/**
	 * @param $rgb
	 */
	public function setRgb( $rgb ) {
		$this->rgb = $rgb;
	}
	
	/**
	 * @param $hsl
	 */
	public function setHsl( $hsl ) {
		$this->hsl = $hsl;
	}
	
	/**
	 * @param bool $lightness
	 * @param int $opacity
	 * @param bool $raw
	 *
	 * @return array|string
	 */
	public function hexToHsla( $lightness = false, $opacity = 1, $raw = false ) {
		$this->rgb = $this->hexToRgba( false, true );
		$hsl       = $this->rgbToHsl( $lightness );
		
		if ( $raw ) {
			return $hsl;
		}
		
		if ( $opacity !== false ) {
			if ( abs( $opacity ) > 1 ) {
				$opacity = 1.0;
			}
			
			return 'hsla( ' . $hsl[0] . ', ' . $hsl[1] . '%, ' . $hsl[2] . '%, ' . $opacity . ')';
		} else {
			return 'hsl(' . $hsl[0] . ', ' . $hsl[1] . '%, ' . $hsl[2] . '%)';
		}
	}
	
	/**
	 * @param bool $opacity
	 * @param bool $raw
	 *
	 * @return array|string
	 */
	public function hexToRgba( $opacity = false, $raw = false ) {
		$default = 'rgb(0,0,0)';
		
		//Return default if no color provided
		if ( empty( $this->hex ) ) {
			return $default;
		}
		
		//Sanitize $this->hex if "#" is provided
		if ( $this->hex[0] == '#' ) {
			$this->hex = substr( $this->hex, 1 );
		}
		
		//Check if color has 6 or 3 characters and get values
		if ( strlen( $this->hex ) == 6 ) {
			$hex = array( $this->hex[0] . $this->hex[1], $this->hex[2] . $this->hex[3], $this->hex[4] . $this->hex[5] );
		} elseif ( strlen( $this->hex ) == 3 ) {
			$hex = array( $this->hex[0] . $this->hex[0], $this->hex[1] . $this->hex[1], $this->hex[2] . $this->hex[2] );
		} else {
			return $default;
		}
		
		//Convert hexadec to rgb
		$rgb = array_map( 'hexdec', $hex );
		
		if ( $raw ) {
			return $rgb;
		}
		
		//Check if opacity is set(rgba or rgb)
		if ( $opacity !== false ) {
			if ( abs( $opacity ) > 1 ) {
				$opacity = 1.0;
			}
			$output = 'rgba(' . implode( ",", $rgb ) . ',' . $opacity . ')';
		} else {
			$output = 'rgb(' . implode( ",", $rgb ) . ')';
		}
		
		//Return rgb(a) color string
		return $output;
	}
	
	/**
	 * @return string
	 */
	public function rgbToHex() {
		return sprintf( "#%02x%02x%02x", $this->rgb[0], $this->rgb[1], $this->rgb[2] );
	}
	
	/**
	 * @param bool $lightness
	 *
	 * @return array
	 */
	public function rgbToHsl( $lightness = false ) {
		list( $r, $g, $b ) = $this->rgb;
		$r   /= 255;
		$g   /= 255;
		$b   /= 255;
		$max = max( $r, $g, $b );
		$min = min( $r, $g, $b );
		$h   = 0;
		$l   = ( $max + $min ) / 2;
		$d   = $max - $min;
		if ( $d == 0 ) {
			$h = $s = 0; // achromatic
		} else {
			$s = $d / ( 1 - abs( 2 * $l - 1 ) ) * 100;
			switch ( $max ) {
				case $r:
					$h = 60 * fmod( ( ( $g - $b ) / $d ), 6 );
					if ( $b > $g ) {
						$h += 360;
					}
					break;
				case $g:
					$h = 60 * ( ( $b - $r ) / $d + 2 );
					break;
				case $b:
					$h = 60 * ( ( $r - $g ) / $d + 4 );
					break;
			}
			$l *= 100;
		}
		
		if ( $lightness ) {
			$percentage = ( absint( $lightness ) / 100 ) * $l;
			if ( $lightness < 0 ) {
				$l = $l - $percentage;
			} else {
				$l = $l + $percentage;
			}
			$l = ( $l > 100 ) ? 100 : $l;
			$l = ( $l < 0 ) ? 0 : $l;
		}
		
		return array( round( $h, 2 ), round( $s, 2 ), round( $l, 2 ) );
	}
	
	/**
	 * @param $hsl
	 *
	 * @return array
	 */
	public function hslToRgb( $hsl ) {
		list( $h, $s, $l ) = $hsl;
		
		$c = ( 1 - abs( 2 * $l - 1 ) ) * $s;
		$x = $c * ( 1 - abs( fmod( ( $h / 60 ), 2 ) - 1 ) );
		$m = $l - ( $c / 2 );
		
		if ( $h < 60 ) {
			$r = $c;
			$g = $x;
			$b = 0;
		} elseif ( $h < 120 ) {
			$r = $x;
			$g = $c;
			$b = 0;
		} elseif ( $h < 180 ) {
			$r = 0;
			$g = $c;
			$b = $x;
		} elseif ( $h < 240 ) {
			$r = 0;
			$g = $x;
			$b = $c;
		} elseif ( $h < 300 ) {
			$r = $x;
			$g = 0;
			$b = $c;
		} else {
			$r = $c;
			$g = 0;
			$b = $x;
		}
		
		$r = ( $r + $m ) * 255;
		$g = ( $g + $m ) * 255;
		$b = ( $b + $m ) * 255;
		
		return array( floor( $r ), floor( $g ), floor( $b ) );
	}
}