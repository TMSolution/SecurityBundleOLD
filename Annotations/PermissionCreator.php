<?php

namespace Core\SecurityBundle\Annotations;

class PermissionCreator
{

    const DEFAULT_MASK = "MaskBuilder::MASK_VIEW, MaskBuilder::MASK_EDIT, MaskBuilder::MASK_MASTER";

    protected $filePath = null;
    protected $tokens = null;
    protected $count = null;
    protected $functions = null;
    protected $useAdded=false;

    public function __construct($filePath)
    {
        $this->setClass($filePath);
    }

    /**
     * 
     * @param string $filePath Class full path
     */
    public function setClass($filePath)
    {
        $this->filePath = $filePath;
        $this->tokens = token_get_all(file_get_contents($this->filePath));
        $this->count = count($this->tokens);
    }

    public function getClassName()
    {
        $classes = Array();
        for ($i = 2; $i < $this->count; $i++) {

            if ($this->tokens[$i - 2][0] == T_CLASS && $this->tokens[$i - 1][0] == T_WHITESPACE && $this->tokens[$i][0] == T_STRING) {

                $classes[] = $this->tokens[$i][1];
            }
        }
        return $classes;
    }

    public function getNamespace()
    {
        $namespace = '';
        for ($i = 1; $i < $this->count; $i++) {

            if ($this->tokens[$i][0] == T_NAMESPACE && $this->tokens[$i + 1][0] == T_WHITESPACE) {
                $counter = $i + 2;

                while ($this->tokens[$counter][0] == T_NS_SEPARATOR || $this->tokens[$counter][0] == T_STRING) {

                    $namespace.=$this->tokens[$counter][1];
                    $counter++;
                }
            }
        }
        return $namespace;
    }

    protected function findEndOfLineToken($counter)
    {
        
    }

    protected function findRangeToken($counter)
    {
        for ($i = $counter - 1; $i > 0; $i--) {
            if (is_array($this->tokens[$i])) {
                $tokenType = $this->tokens[$i][0];
                if ($tokenType == T_PUBLIC) {
                    return $i;
                }
            }
        }
    }

    protected function findDocCommentToken($counter)
    {

        for ($i = $counter - 1; $i > 0; $i--) {
            if (is_array($this->tokens[$i])) {
                if ($this->tokens[$i] == "}") {
                    return false;
                }
                $tokenType = $this->tokens[$i][0];
                if ($tokenType == T_DOC_COMMENT) {
                    return ["number" => $i, "value" => $this->tokens[$i][1]];
                    // $tokenType !== 310 && $tokenType !== 318 && $tokenType !== 379 && $
                    // tokenType !== 377 && 
                } else if ($tokenType !== 377) { // ?
                    return false;
                }
            } else {
                
            }
        }
    }

    protected function findFunctionNameToken($counter)
    {
        for ($i = $counter + 1; $i <= count($this->tokens); $i++) {
            if (is_array($this->tokens[$i])) {
                if ($this->tokens[$i][0] == T_STRING) {
                    if (strstr($this->tokens[$i][1], "Action")) {
                        return true;
                    }
                }
            } else {
                
            }
        }
    }

    public function isSecurityAnnotation($docComment)
    {
        if (strstr($docComment, "@Permissions")) {
            return true;
        }
        return false;
    }

    public function addSecurityAnnotation($docComment)
    {
        $docComment = substr($docComment, 0, -3);
        $docComment.="\r\n * @Permissions(rights={" . self::DEFAULT_MASK . "}) \r\n */";
        return $docComment;
    }

    public function createSecurityAnnotation()
    {
        return "/**\r\n * @Permissions(rights={" . self::DEFAULT_MASK . "})  \r\n */ \r\n";
    }

    protected function calculateNewComment($rangeTokenNumber)
    {
        if ($rangeTokenNumber) {

            $token = $this->findDocCommentToken($rangeTokenNumber);
            if ($token != false) {
                //["tokenNumber" => $i,"tokenValue"=> $this->tokens[$i][1]];
                $tokenValue = $token["value"];
                if ($tokenValue) {

                    if (!$this->isSecurityAnnotation($tokenValue)) {

                        $tokenValue = $this->addSecurityAnnotation($tokenValue);
                        $this->tokens[$token["number"]][1] = $tokenValue;
                    }
                }
            } else {
                $tokenValue = $this->createSecurityAnnotation();
                $this->tokens[$rangeTokenNumber][1] = $tokenValue . $this->tokens[$rangeTokenNumber][1];
            }
        }
    }
    
    public function addUseStatements($token, $counter) {
       
       if ($this->useAdded == true) {
           return;
       } 
       
        if($token[0] == T_USE) {   
            
            
            $this->tokens[$counter][1]=
                    "\n" . "use Core\SecurityBundle\Annotations\Permissions;"
                    . "\n" . "use Symfony\Component\Security\Acl\Permission\MaskBuilder;"
                    . "\n". $this->tokens[$counter][1];     
              
            $this->useAdded=true;
        }
    }

    public function annotate()
    {


        $counter = -1;
        foreach ($this->tokens as $token) {
            $counter++;
            if (is_array($token)) {
                $this->addUseStatements($token, $counter);
                if ($token[0] == T_FUNCTION) {
                    if ($this->findFunctionNameToken($counter)) {
                        $rangeTokenNumber = $this->findRangeToken($counter);
                        $this->calculateNewComment($rangeTokenNumber);
                    }
                }
            }
        }

        $text = "";
        foreach ($this->tokens as $token) {
            if (is_array($token)) {

                $text.=$token[1];
            } else {
                $text.=$token;
            }
        }

        return $text;
    }

    /* @TODO PL
     * Dopisać metody czytające właściwości i metody obiektu
     */
}

/*

 * jesli istnial podmien token
 * jesli nie istnial znajdz range doklej przed niego
 * forache zczytaj wszystkie linie
 * 
 * 
 * 
 * 
 *  */
