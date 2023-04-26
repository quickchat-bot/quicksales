<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author        Varun Shoor
 *
 * @package        SWIFT
 * @copyright    Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.opencart.com.vn/license
 * @link        http://www.opencart.com.vn
 *
 * ###############################################
 */

/**
 * The Image Resizing Class
 * 
 * @author Daniel M. Story - admin@danstory.com (http://www.danstory.com)
 */
class SWIFT_ImageResize extends SWIFT_Library
{
    // Core Constants
    const TYPE_SAME = 'same';
    const TYPE_GIF = 'gif';
    const TYPE_JPEG = 'jpeg';
    const TYPE_PNG = 'png';

    /**
     * Holds the image path
     */
    private $_imagePath = '';

    /**
     * The limit of the image width
     */
    private $_sizeLimitX = 100;

    /**
     * The limit of the image height
     */
    private $_sizeLimitY = 100;

    /**
     * Holds the image resource
     */
    private $_imageResource = '';

    /**
     * If true it keeps the image proportions when resized
     */
    private $_keepProportions = true;
    private $_keepBothProportions = false;

    /**
     * Holds the resized image resource
     */
    private $_resizedResource = '';

    /**
     * Can be JPG, GIF, PNG, or SAME (same will save as old type)
     */
    private $_outputType = self::TYPE_SAME;

    private $_hasGD = false;


    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_imagePath The Image Pathe
     * @throws SWIFT_Image_Exception If the Class fails to Load
     */
    public function __construct($_imagePath)
    {
        parent::__construct();

        if (!$this->SetImagePath($_imagePath))
        {
            throw new SWIFT_Image_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->CheckGD();
    }

    /**
     * Destructor
     *
     * @author Varun Shoore
     */
    public function __destruct()
    {
        parent::__destruct();

        if ($this->GetResizedResource() != '')
        {
            imagedestroy($this->GetResizedResource());
        }
    }

    /**
     * Set the Image path
     * 
     * @author Varun Shoor
     * @param string $_imagePath The Image Path
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Image_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetImagePath($_imagePath)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Image_Exception(SWIFT_CLASSNOTLOADED);
    
            return false;
        } else if (empty($_imagePath)) {
            throw new SWIFT_Image_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->_imagePath = $_imagePath;

        return true;
    }

    /**
     * Retrieve the Image Path
     * 
     * @author Varun Shoor
     * @return mixed "_imagePath" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Image_Exception If the Class is not Loaded
     */
    public function GetImagePath()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Image_Exception(SWIFT_CLASSNOTLOADED);

            $this->SetIsClassLoaded(false);
    
            return false;
        }

        return $this->_imagePath;
    }

    /**
     * Check for GD Library
     * 
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Image_Exception If the Class is not Loaded
     */
    protected function CheckGD()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Image_Exception(SWIFT_CLASSNOTLOADED);
    
            return false;
        }

        if (function_exists('gd_info'))
        {
            $this->_hasGD = true;

            return true;
        }

        return false;
    }

    /**
     * Check to see if it has GD
     * 
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Image_Exception If the Class is not Loaded
     */
    public function HasGD()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Image_Exception(SWIFT_CLASSNOTLOADED);
    
            return false;
        }

        return $this->_hasGD;
    }

    /**
     * Set the Image Resource
     * 
     * @author Varun Shoor
     * @param resource $_imageResource The Image Resource
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Image_Exception If the Class is not Loaded
     */
    protected function SetImageResource($_imageResource)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Image_Exception(SWIFT_CLASSNOTLOADED);
    
            return false;
        }

        $this->_imageResource = $_imageResource;

        return true;
    }

    /**
     * Get the Image Resource
     * 
     * @author Varun Shoor
     * @return mixed "_imageResource" (RESOURCE) on Success, "false" otherwise
     * @throws SWIFT_Image_Exception If the Class is not Loaded
     */
    public function GetImageResource()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Image_Exception(SWIFT_CLASSNOTLOADED);
    
            return false;
        }

        return $this->_imageResource;
    }

    /**
     * Set the Resize Image Resource
     * 
     * @author Varun Shoor
     * @param resource $_resizedResource The Resized Image Resource
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Image_Exception If the Class is not Loaded
     */
    protected function SetResizedResource($_resizedResource)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Image_Exception(SWIFT_CLASSNOTLOADED);
    
            return false;
        }

        $this->_resizedResource = $_resizedResource;

        return true;
    }

    /**
     * Get the Resized Image Resource
     * 
     * @author Varun Shoor
     * @return mixed "_resizedResource" (RESOURCE) on Success, "false" otherwise
     * @throws SWIFT_Image_Exception If the Class is not Loaded
     */
    public function GetResizedResource()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Image_Exception(SWIFT_CLASSNOTLOADED);
    
            return false;
        }

        return $this->_resizedResource;
    }

    /**
     * Check to see if it is a valid output type
     * 
     * @author Varun Shoor
     * @param mixed $_outputType The Output Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidOutputType($_outputType)
    {
        if ($_outputType == self::TYPE_SAME || $_outputType == self::TYPE_GIF || $_outputType == self::TYPE_JPEG || $_outputType == self::TYPE_PNG)
        {
            return true;
        }

        return false;
    }

    /**
     * Sets the Output Type
     * 
     * @author Varun Shoor
     * @param mixed $_outputType The Output Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Image_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetOutputType($_outputType)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Image_Exception(SWIFT_CLASSNOTLOADED);
    
            return false;
        } else if (!self::IsValidOutputType($_outputType)) {
            throw new SWIFT_Image_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->_outputType = $_outputType;

        return true;
    }

    /**
     * Retrieve the currently set Ouput Type
     * 
     * @author Varun Shoor
     * @return mixed "_outputType" (CONSTANT) on Success, "false" otherwise
     * @throws SWIFT_Image_Exception If the Class is not Loaded
     */
    public function GetOutputType()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Image_Exception(SWIFT_CLASSNOTLOADED);
    
            return false;
        }

        return $this->_outputType;
    }

    /**
     * Set the Size Limit
     * 
     * @author Varun Shoor
     * @param int $_sizeLimitX The X Size Limit
     * @param int $_sizeLimitY The Y Size Limit
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Image_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetSize($_sizeLimitX, $_sizeLimitY)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Image_Exception(SWIFT_CLASSNOTLOADED);
    
            return false;
        }

        $_sizeLimitX = $_sizeLimitX;
        $_sizeLimitY = $_sizeLimitY;

        if (empty($_sizeLimitX) || empty($_sizeLimitY))
        {
            throw new SWIFT_Image_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->_sizeLimitX = $_sizeLimitX;
        $this->_sizeLimitY = $_sizeLimitY;

        return true;
    }

    /**
     * Retrieve the currently set size limit
     * 
     * @author Varun Shoor
     * @return mixed array(_sizeLimitX, _sizeLimitY) on Success, "false" otherwise
     * @throws SWIFT_Image_Exception If the Class is not Loaded
     */
    public function GetSize()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Image_Exception(SWIFT_CLASSNOTLOADED);
    
            return false;
        }

        return array($this->_sizeLimitX, $this->_sizeLimitY);
    }

    /**
     * Set the Keep Both Proportions flag
     * 
     * @author Varun Shoor
     * @param bool $_keepBothProportions The Keep Proportions Flag
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Image_Exception If the Class is not Loaded
     */
    public function SetKeepBothProportions($_keepBothProportions)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Image_Exception(SWIFT_CLASSNOTLOADED);
    
            return false;
        }

        $this->_keepBothProportions = $_keepBothProportions;

        return true;
    }

    /**
     * Retrieve the Keep Both Proportions flag
     * 
     * @author Varun Shoor
     * @return mixed "_keepBothProportions" on Success, "false" otherwise
     * @throws SWIFT_Image_Exception If the Class is not Loaded
     */
    public function GetKeepBothProportions()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Image_Exception(SWIFT_CLASSNOTLOADED);
    
            return false;
        }

        return $this->_keepBothProportions;
    }

    /**
     * Set the Keep Proportions flag
     * 
     * @author Varun Shoor
     * @param bool $_keepProportions The Keep Proportions Flag
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Image_Exception If the Class is not Loaded
     */
    public function SetKeepProportions($_keepProportions)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Image_Exception(SWIFT_CLASSNOTLOADED);
    
            return false;
        }

        $this->_keepProportions = $_keepProportions;

        return true;
    }

    /**
     * Retrieve the Keep Proportions flag
     * 
     * @author Varun Shoor
     * @return mixed "_keepProportions" on Success, "false" otherwise
     * @throws SWIFT_Image_Exception If the Class is not Loaded
     */
    public function GetKeepProportions()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Image_Exception(SWIFT_CLASSNOTLOADED);
    
            return false;
        }

        return $this->_keepProportions;
    }

    /**
     * Resize the currently loaded Image
     * 
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Image_Exception If the Class is not Loaded
     */
    public function Resize()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Image_Exception(SWIFT_CLASSNOTLOADED);
    
            return false;
        }

        if (!$this->HasGD())
        {
            return false;
        }

        $_pathInfoContainer = pathinfo($this->GetImagePath());

        // This is going to get the image width, height, and format
        list ($_imageWidth, $_imageHeight, $_imageType, $_imageAttribute) = getimagesize($this->GetImagePath());

        // Make sure it was loaded correctly
        if ($_imageHeight != 0 || $_imageWidth != 0)
        {
            switch ($_imageType)
            {
                // GIF
                case IMAGETYPE_GIF:
                    if (!function_exists('imagecreatefromgif')) {
                        throw new SWIFT_Exception('imagecreatefromgif function does not exist. Please ensure GD is compiled with support for GIF format.');
                    }

                    $this->SetImageResource(imagecreatefromgif($this->GetImagePath()));

                    if ($this->GetOutputType() == self::TYPE_SAME)
                    {
                        $this->SetOutputType(self::TYPE_GIF);
                    }

                    break;

                // JPG
                case IMAGETYPE_JPEG2000:
                case IMAGETYPE_JPEG:
                    if (function_exists('imagecreatefromjpeg')) {
                        $jpg = call_user_func('imagecreatefromjpeg', $this->GetImagePath());
                        $this->SetImageResource($jpg);
                    }

                    if ($this->GetOutputType() == self::TYPE_SAME)
                    {
                        $this->SetOutputType(self::TYPE_JPEG);
                    }

                    break;

                // PNG
                case IMAGETYPE_PNG:
                    if (!function_exists('imagecreatefrompng')) {
                        throw new SWIFT_Exception('imagecreatefrompng function does not exist. Please ensure GD is compiled with support for PNG format.');
                    }

                    $this->SetImageResource(imagecreatefrompng($this->GetImagePath()));

                    if ($this->GetOutputType() == self::TYPE_SAME)
                    {
                        $this->SetOutputType(self::TYPE_PNG);
                    }

                    break;
            }

            // It wasn't able to load the image
            if ($this->GetImageResource() == '')
            {
                return false;
            }
        } else {
            return false;
        }

        if ($this->GetKeepBothProportions() == true)
        {
            if (($_imageWidth - $this->_sizeLimitX) > ($_imageHeight - $this->_sizeLimitY)) {
                // If the width of the img is greater than the size limit we scale by width
                $_scaleX = $this->_sizeLimitX / $_imageWidth;
                $_scaleY = $_scaleX;

            // If the height of the img is greater than the size limit we scale by height
            } else {
                $_scaleX = $this->_sizeLimitY / $_imageHeight;
                $_scaleY = $_scaleX;
            }
        } else if ($this->GetKeepProportions() == true) {
            // If the width of the img is greater than the size limit we scale by width
            $_scaleX = $this->_sizeLimitX / $_imageWidth;
            $_scaleY = $_scaleX;
        } else {
            // Just make the image fit the image size limit
            $_scaleX = $this->_sizeLimitX / $_imageWidth;
            $_scaleY = $this->_sizeLimitY / $_imageHeight;

            // Don't make it so it streches the image
            if ($_scaleX > 1 )
            {
                $_scaleX = 1;
            }

            if ($_scaleY > 1)
            {
                $_scaleY = 1;
            }
        }
        
//        echo $_scaleX . 'x' . $_scaleY . '<br />';
//        echo $_imageWidth . 'x' . $_imageHeight . '<br />';

        $_newWidth = $_imageWidth * $_scaleX;
        $_newHeight = $_imageHeight * $_scaleY;
        
//        echo $_newWidth . 'x' . $_newHeight;

        // Creates an image resource, with the width and height of the size limits (or new resized proportion )
        $this->SetResizedResource(imagecreatetruecolor($_newWidth, $_newHeight));

        // Helps in the quality of the image being resized
        if (function_exists('imageantialias'))
        {
            imageantialias($this->GetResizedResource(), true);
        }

        // Resize the iamge onto the resized resource
        imagecopyresampled($this->GetResizedResource(), $this->GetImageResource(), 0, 0, 0, 0, $_newWidth, $_newHeight, $_imageWidth, $_imageHeight);

        // Destory old image resource
        imagedestroy($this->GetImageResource());

        return true;
    }

    /**
     * Save the Resized Image
     * 
     * @author Varun Shoor
     * @param string $_filePath The File Path
     * @param bool $_output (OPTIONAL) Whether to dispatch output
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Image_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Save($_filePath, $_output = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Image_Exception(SWIFT_CLASSNOTLOADED);
    
            return false;
        } else if (empty($_filePath)) {
            throw new SWIFT_Image_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        // If GD isnt loaded then attempt to save the file as is
        if (!$this->HasGD())
        {
            $_filePointer = fopen($_filePath, 'w+');
            fwrite($_filePointer, file_get_contents($this->GetImagePath()));
            fclose($_filePointer);

            return false;
        }

        if ($this->GetResizedResource() == '') {
            throw new SWIFT_Image_Exception(SWIFT_INVALIDDATA);

            return false;
        }
        
        if ($_output)
        {
            header('Last-Modified: ' . date('D, d M Y H:i:s', DATENOW) . ' GMT'); 
            header('Cache-Control: public');
        }

        switch ($this->GetOutputType())
        {
            case self::TYPE_GIF:
                imagegif($this->GetResizedResource(), $_filePath);
                
                if ($_output)
                {
                    header('Content-type: image/gif');
                    echo file_get_contents($_filePath);
                }

                break;

            case self::TYPE_JPEG:
                if (function_exists('imagejpeg')) {
                    call_user_func('imagejpeg', $this->GetResizedResource(), $_filePath, 100);
                }

                if ($_output)
                {
                    header('Content-type: image/jpg');
                    echo file_get_contents($_filePath);
                }

                break;

            case self::TYPE_PNG:
                imagepng($this->GetResizedResource(), $_filePath, 0);
                
                if ($_output)
                {
                    header('Content-type: image/png');
                    echo file_get_contents($_filePath);
                }

                break;

            default:
                throw new SWIFT_Image_Exception(SWIFT_INVALIDDATA);

                break;
        }

        return true;
    }

    /**
     * Output the Resized Image
     * 
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Image_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Output()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Image_Exception(SWIFT_CLASSNOTLOADED);
    
            return false;
        }

        header('Last-Modified: ' . date('D, d M Y H:i:s', DATENOW) . ' GMT'); 
        header('Cache-Control: public');

        // If GD isnt loaded then attempt to output the file as is
        if (!$this->HasGD())
        {
            echo file_get_contents($this->GetImagePath());

            return false;
        }

        if ($this->GetResizedResource() == '') {
            switch ($this->GetOutputType())
            {
                case self::TYPE_GIF:
                    header('Content-type: image/gif');

                    echo file_get_contents($this->GetImagePath());
                    break;

                case self::TYPE_JPEG:
                    header('Content-type: image/jpg');

                    echo file_get_contents($this->GetImagePath());
                    break;

                case self::TYPE_PNG:
                    header('Content-type: image/png');

                    echo file_get_contents($this->GetImagePath());
                    break;

                case self::TYPE_SAME:
                    $_pathInfoContainer = pathinfo($this->GetImagePath());
                    header('Content-type: image/' . $_pathInfoContainer['extension']);

                    echo file_get_contents($this->GetImagePath());
                    break;
            }
            
            return false;
        }

        switch ($this->GetOutputType())
        {
            case self::TYPE_GIF:
                header('Content-type: image/gif');

                imagegif($this->GetResizedResource());
                break;

            case self::TYPE_JPEG:
                header('Content-type: image/jpg');

                if (function_exists('imagejpeg')) {
                    call_user_func('imagejpeg', $this->GetResizedResource());
                }
                break;

            case self::TYPE_PNG:
                header('Content-type: image/png');

                imagepng($this->GetResizedResource());
                break;

            case self::TYPE_SAME:
                throw new SWIFT_Image_Exception(SWIFT_INVALIDDATA);

                break;
        }

        return true;
    }

    /**
     * Retrieve the Resized Image Data
     * 
     * @author Varun Shoor
     * @return mixed "_imageContents" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Image_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Get()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Image_Exception(SWIFT_CLASSNOTLOADED);
    
            return false;
        }

        // If GD isnt loaded then attempt to retrieve the file as is
        if (!$this->HasGD())
        {
            return file_get_contents($this->GetImagePath());

            return false;
        }

        if ($this->GetResizedResource() == '') {
            throw new SWIFT_Image_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_imageContents = false;

        switch ($this->GetOutputType())
        {
            case self::TYPE_GIF:
                ob_start();
                imagegif($this->GetResizedResource());

                $_imageContents = ob_get_contents();
                ob_end_clean();

                break;

            case self::TYPE_JPEG:
                ob_start();

                if (function_exists('imagejpeg')) {
                    call_user_func('imagejpeg', $this->GetResizedResource());
                }
                $_imageContents = ob_get_contents();
                ob_end_clean();

                break;

            case self::TYPE_PNG:
                ob_start();

                imagepng($this->GetResizedResource());

                $_imageContents = ob_get_contents();
                ob_end_clean();

                break;

            case self::TYPE_SAME:
                throw new SWIFT_Image_Exception(SWIFT_INVALIDDATA);

                break;
        }

        return $_imageContents;
    }
}
?>
