<?php

namespace UIS\Mvf;

class ValidationResult implements \JsonSerializable
{
    /**
     *  Errors array map
     *  @var array
     */
    private $errorsMap = array();

    /**
     *  Add error to map
     *  @param    string 						  $key
     *  @param    string|UIS_Mvf_Validator_Error  $error
     *  @return   void
     */
    public function addError( $key , $error, $params = array() ) {
        if ( $error instanceof ValidationError ) {
            $this->errorsMap[$key] = $error ;
        }
        if ( $error instanceof ValidationResult ) {
            $this->errorsMap[$key] = $error ;
        }
        else if( is_string( $error )  ){
            $vError = new ValidationError();
            $vError->setError( $error );
            $vError->setParams( $params );
            $this->errorsMap[$key] = $vError ;
        }
    }

    public function removeError ( $key ) {
        if (  isset( $this->errorsMap[ $key ] )  ) {
            unset( $this->errorsMap[ $key ] );
        }
    }

    /**
     *  Check is validation result
     *  or some item valid
     *  @param   string   $key
     *  @return  boolean
     *  @TODO Change is valid code, it each time translate all keys, but no need ...
     */
    public function isValid( $key = null ) {

        if( $key == null ) {
            return empty( $this->errors() );
        }
        else {
            if( !isset( $this->errorsMap[ $key ] ) ) {
                return true;
            }
            return false;
        }
    }

    /**
     *  Get all errors array
     *  @return array
     */
    public function errors () {

        $return = array();
        foreach( $this->errorsMap AS $key => $error ) {
            if ( $error instanceof ValidationResult ) {
                $subErrors = $error->errors();
                foreach( $subErrors AS $errorKey => $error ){
                    $return[$key.'_'.$errorKey] = $error;
                }
            }
            else{
                $return[$key] = $this->error( $key );
            }
        }
        return $return;
    }


    /**
     *  Get error by key
     *  @param   string   $key
     *  @return  string   Error message
     */
    public function error( $key ) {

        if ( !isset($this->errorsMap[$key]) ) {
            return '';
        }
        $error  = $this->errorsMap[$key];
        $string = $error->errorMessage();
        $errorParams = $error->getParams();

        preg_match_all( '/\{[A-Z0-9_\.]+\}/i' , $string , $matches );

        foreach(  $matches[0]  AS $key => $value ) {
            $key = substr( $value , 1 , strlen ( $value )-2  ); // returns "d"
            $mlValue= empty($errorParams) ? trans( $key ) : trans( $key, $errorParams );
            $string =  str_replace (   "{".$key."}"  ,  $mlValue   , $string );
        }
        return $string;

    }



    public function __toString()
    {
        //echo "___";
        print_r($this->errorsMap);
        return "";
    }

    /******************************************************************************************************************/
    /******************************************************************************************************************/
    /******************************************************************************************************************/

    public function jsonSerialize()
    {
        $errors = $this->errors();
        if (empty($errors)) {
            return null;
        }
        return $errors;
    }
}
