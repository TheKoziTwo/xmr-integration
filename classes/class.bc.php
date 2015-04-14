<?php if(!defined('APP')) exit;
/**
 * BC Math class
 * 
 * Wrapper for bc math and new useful functions
 * 
 * @author     TheKoziTwo <thekozitwo@gmail.com>
 * @copyright  2015
 * @license    Public Domain
 */ 
class bc {
    
    /**
     * Mathematic operations using +,-,*,/,^,%
     * 
     * @param   string|float    number
     * @param   string          operator
     * @param   string|float    number
     * @param   bool            true = round result, false = no rounding
     */ 
    public static function op($num,$op,$num2,$precision = 0,$round = false,$strip_trailing_zeros = true)
    {                
        switch($op)
        {
            case '+':
                $res = self::add($num,$num2,$precision);
                break;
            case '-':
                $res = self::sub($num,$num2,$precision);
                break;
            case '*':
                $res = self::mul($num,$num2,$precision);
                break;
            case '/':
                $res = self::div($num,$num2,$precision);
                break;
            case '^':
                $res = self::pow($num,$num2,$precision);
                break;
            case '%':
                $res = self::mod($num,$num2);
                break;
        }
        return $round ? self::round($res,$precision,$strip_trailing_zeros) : $res;   
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Compare two numbers using default operators: > , < , >= , <= , != and ==
     * 
     * DO NOT use this function to compare against -0. 
     * This function cannot compare -0.00, negative zero does not compare equal to positive zero. 
     * Thus, to check if zero we use bc::is_zero() instead. 
     * 
     * @param   string|float    number
     * @param   string          operator 
     * @param   string|float    number 
     * @return  bool            true/false (like if it was an if sentence)
     */ 
    public static function is($num,$op,$num2)
    {
        $num  = (string) $num;
        $num2 = (string) $num2;
        
        $res = bccomp($num, $num2);
        switch($op)
        {
            case '>':
                return (bool) ($res === 1);
            case '<':
                return (bool) ($res === -1);
            case '>=':
                return (bool) ($res === 0 OR $res === 1); 
            case '<=':
                return (bool) ($res === 0 OR $res === -1); 
            case '!=':
                return (bool) ($res !== 0);
            default: // ==
                return (bool) ($res === 0);
        }
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Check if number is zero (0, 0.00, 0.0000, -0, -0.00 etc)
     * 
     * Works with both negative (-0.00) and positive (0.00)
     * 
     * @param   string|float    the number to check
     * @return  bool            true if zero
     */ 
    public static function is_zero($num)
    {
        $num = (string) $num;

        if (@$num{0}=="-")
        {
            return (bool) ((bccomp($num, '-0.0') === 0) OR (bccomp($num, '-0') === 0));
        }
        else
        {
           return (bool) (bccomp($num, '0.0') === 0);
        }
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Floor : Round fractions down (same as floor() in php)
     * 
     * @param   string|float    the number to floor
     * @return  string          Returns the next lowest "integer" value by rounding 
     *                          down value if necessary.
     */ 
    public static function floor($number)
    {
        if ($number[0] != '-')
        {
            return bcadd($number, 0, 0);
        }
    
        return bcsub($number, 1, 0);
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Strip trailing zeros (and . if any)
     * 
     * This is done using regex since rtrim does not work for all numbers
     * 
     * @author  Rastislav Bostik <rastislav.bostik@bwd21.cz>
     * @link    http://www.php.net/manual/en/function.bcscale.php#107259
     * @param   string|float      number
     * @return  string            number without trailing zeros
     */ 
    public static function strip_trailing_zeros($input) 
    {
        $patterns = array('/[\.][0]+$/','/([\.][0-9]*[1-9])([0]*)$/');
        $replaces = array('','$1');
        return preg_replace($patterns,$replaces,$input);
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Addition between two values (number + number)
     * 
     * @param   string|float      number
     * @param   string|float      number
     * @return  string            number (result of the addition)
     */ 
    public static function add($num,$num2)
    {
        $num  = (string) $num;
        $num2 = (string) $num2;
        
        return bcadd($num, $num2);
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Subtraction between two values (number - number)
     * 
     * @param   string|float      number
     * @param   string|float      number
     * @return  string            number (result of the subtraction)
     */ 
    public static function sub($num,$num2)
    {
        $num  = (string) $num;
        $num2 = (string) $num2;
        return bcsub($num, $num2);    
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Multiple two values (number * number)
     * 
     * @param   string|float      number
     * @param   string|float      number
     * @return  string            number (result of the multiplication)
     */ 
    public static function mul($num,$num2)
    {
        $num  = (string) $num;
        $num2 = (string) $num2;
        return bcmul($num, $num2);
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Division between two values (number / number)
     * 
     * @param   string|float      number
     * @param   string|float      number
     * @return  string            number (result of the division)
     */ 
    public static function div($num,$num2)
    {
        $num  = (string) $num;
        $num2 = (string) $num2;
        return bcdiv($num, $num2);
    }

    // ------------------------------------------------------------------------

    /**
     * Get modulus of two values (number % number = mod)
     * 
     * @param   string|float      number
     * @param   string|float      number
     * @return  string            number (result from modulus)
     */ 
    public static function mod($num,$num2)
    {
        $num  = (string) $num;
        $num2 = (string) $num2;
        return bcmod($num, $num2);
    }

    // ------------------------------------------------------------------------

    /**
     * Raise a number to another (number^number) (e.g 5^5 = 25)
     * 
     * @param   string|float      number
     * @param   string|float      number
     * @return  string            number  
     */ 
    public static function pow($num,$num2)
    {
        $num  = (string) $num;
        $num2 = (string) $num2;
        return bcpow($num, $num2);
    } 
    
    // ------------------------------------------------------------------------
    
    /**
     * Return absolute value of number (like php's native abs function) 
     * (e.g convert a negative number into a postive number)
     * 
     * @param   string|float     number that will be returned as absolute value.
     * @return  string           the number as absolute value
     */ 
    public static function abs($value)
    {
        $value = (string) $value;
        return (string) (bccomp($value, '0') < 0) ? substr($value, 1) : $value;
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Round a value to the specific amount of decimals.
     * 
     * @param   string|float    value to round
     * @param   int             precision (the number of decimals to round to)
     * @param   bool            true to strip trailing errors (e.g 0.0011000 will return: 0.0011)
     * @return  string          the value rounded
     */ 
    public static function round($value, $precision = 8,$strip_trailing_zeros = true) 
    {
        $value = (string) $value;
        
        if (false !== ($pos = strpos($value, '.')) && (strlen($value) - $pos - 1) > $precision) 
        {
            $zeros = str_repeat("0", $precision);
            $value = bcadd($value, "0.{$zeros}5", $precision);
        }
        
        // Force zeros:
        if( ! $strip_trailing_zeros)
        {
            $pos = strpos($value,'.');
            // If decimals found:
            if($pos !== false)
            {
                // grab the decimals only
                $decimals = substr($value,$pos+1,strlen($value));
                
                // add missing zeros to the decimal
                while(strlen($decimals) < $precision)
                {
                    $decimals.='0';
                }
                // concate digits and decimals to get final value with all decimals added
                $digits = substr($value,0,$pos);
                $value = $digits.'.'.$decimals;
            }
            else // add decimal:
            {
                // need to be a least 1 decimal for this to make sense:
                if($precision>0)
                {
                    $value = $value.'.'.str_repeat('0',$precision);
                }
            }    
        }
        
        if($strip_trailing_zeros)
        {
            $value = self::strip_trailing_zeros($value);
        }
                
        return (string) $value;
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Count how many decimals are in a number.
     * 
     * @example 1.55 = 2 
     *          1.29592 = 5
     *          1 = 0
     * 
     * @param   string      number
     * @return  int         the amount of decimals
     */ 
    public static function count_decimals($num,$do_not_count_zeros = true)
    {
        if($do_not_count_zeros) $num = rtrim($num,'0');
        return strlen(substr(strrchr($num, "."), 1));
    }  
    
}