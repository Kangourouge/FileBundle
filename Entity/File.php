<?php

namespace KRG\FileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gaufrette\Filesystem;
use KRG\FileBundle\Filesystem\Adapter\AdapterInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeExtensionGuesser;
use KRG\FileBundle\Driver\DriverInterface;

/**
 * Abstract File Entity
 * @ORM\MappedSuperclass
 */
abstract class File implements FileInterface {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;
    
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $path;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $url;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $checksum;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $mimeType;

    /**
     * @ORM\Column(type="decimal")
     * @var integer
     */
    protected $size;

	/**
	 * @ORM\Column(type="decimal", nullable=true)
	 * @var integer
	 */
	protected $height;

	/**
	 * @ORM\Column(type="decimal", nullable=true)
	 * @var integer
	 */
	protected $width;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $adapter;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * File constructor.
     */
    public function __construct()
    {
        $this->adapter = 'local';
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->getUrl();
    }

    /**
     * Clone
     */
    public function __clone() {
        $this->id = null;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * Set name
     *
     * @param string $name
     * @return File
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set path
     *
     * @param string $path
     *
     * @return File
     */
    public function setPath($path) {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return File
     */
    public function setUrl($url) {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Set checksum
     *
     * @param string $checksum
     * @return File
     */
    public function setChecksum($checksum)
    {
        $this->checksum = $checksum;

        return $this;
    }

    /**
     * Get checksum
     *
     * @return string
     */
    public function getChecksum()
    {
        return $this->checksum;
    }

    /**
     * Set mimeType
     *
     * @param string $mimeType
     *
     * @return File
     */
    public function setMimeType($mimeType) {

        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * Get mimeType
     *
     * @return string
     */
    public function getMimeType() {
        return $this->mimeType;
    }

    /**
     * Set size
     *
     * @param string $size
     *
     * @return File
     */
    public function setSize($size) {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return string
     */
    public function getSize() {
        return $this->size;
    }

    /**
     * @return string
     */
    public function getExtension() {
        $mimeTypeGuesser = new MimeTypeExtensionGuesser;
        return $mimeTypeGuesser->guess($this->mimeType);
    }

    /**
     * @param int $dec
     * @return string
     */
    public function getHumanReadableSize($dec = 2) {
        $sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $factor = floor((strlen($this->size) - 1) / 3);
        return sprintf("%.{$dec}f %s", $this->size / pow(1024, $factor), @$sizes[$factor]);
    }

    /**
     * @return bool
     */
    public function isImage() {
        return substr($this->mimeType, 0, 6) === 'image/';
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getUrlFromAdapter() {
        if ($this->filesystem->getAdapter() instanceof AdapterInterface) {
            return $this->filesystem->getAdapter()->getUrl($this->path);
        }
        return null;
    }
    
    /**
     * Get path
     *
     * @return string
     */
    public function getThumbnail() {
        return $this->filesystem ? $this->filesystem->getThumbnail($this->path) : null;
    }

    /**
     * @return array
     */
    public function getMetadata() {
        return array(
            'id'  => $this->id,
            'name' => $this->name,
            'src' => $this->getUrl(),
            'mimeType' => $this->mimeType,
            'size' => $this->getHumanReadableSize(),
            'extension' => $this->getExtension(),
            'type' => strstr($this->mimeType, '/', true)
        );
    }

    /**
     * @return string
     */
    function getAdapter() {
        return $this->adapter;
    }

    /**
     * @param string $driver
     * @return FileInterface
     */
    function setAdapter($driver) {
        $this->adapter = $driver;
        return $this;
    }

    /**
     * @param Filesystem $filesystem
     */
    public function setFilesystem(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        return $this;
    }

	/**
	 * @return int
	 */
	public function getHeight()
	{
		return $this->height;
	}

	/**
	 * @return int
	 */
	public function getWidth()
	{
		return $this->width;
	}

	/**
	 * @param int $width
	 */
	public function setWidth($width)
	{
		$this->width = $width;
	}

	/**
	 * @param int $height
	 * @return File
	 */
	public function setHeight($height)
	{
		$this->height = $height;
		return $this;
	}


}
