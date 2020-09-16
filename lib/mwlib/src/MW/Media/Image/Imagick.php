<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2018
 * @package MW
 * @subpackage Media
 */


namespace Aimeos\MW\Media\Image;


/**
 * Default image class using ImageMagick.
 *
 * @package MW
 * @subpackage Media
 */
class Imagick
	extends \Aimeos\MW\Media\Image\Base
	implements \Aimeos\MW\Media\Image\Iface
{
	private $image;
	private $options;
	private $wmimg;


	/**
	 * Initializes the new image object.
	 *
	 * @param string $content File content
	 * @param string $mimetype Mime type of the media data
	 * @param array $options Associative list of configuration options
	 * @throws \Aimeos\MW\Media\Exception If image couldn't be retrieved from the given file name
	 */
	public function __construct( $content, $mimetype, array $options )
	{
		parent::__construct( $mimetype );

		try
		{
			$this->image = new \Imagick( [] );
			$this->image->readImageBlob( $content );

			if( isset( $options['image']['watermark'] ) )
			{
				$this->wmimg = new \Imagick( [] );
				$this->wmimg->readImage( $options['image']['watermark'] );
			}
		}
		catch( \Exception $e )
		{
			throw new \Aimeos\MW\Media\Exception( $e->getMessage() );
		}

		$this->options = $options;
	}


	/**
	 * Cleans up
	 */
	public function __destruct()
	{
		if( $this->image ) {
			$this->image->clear();
		}
	}


	/**
	 * Clone resources
	 */
	public function __clone()
	{
		$this->image = clone $this->image;
	}


	/**
	 * Returns the height of the image
	 *
	 * @return integer Height in pixel
	 */
	public function getHeight()
	{
		return $this->image->getImageHeight();
	}


	/**
	 * Returns the width of the image
	 *
	 * @return integer Width in pixel
	 */
	public function getWidth()
	{
		return $this->image->getImageWidth();
	}


	/**
	 * Stores the media data at the given file name.
	 *
	 * @param string|null $filename File name to save the data into or null to return the data
	 * @param string|null $mimetype Mime type to save the content as or null to leave the mime type unchanged
	 * @return string|null File content if file name is null or null if data is saved to the given file name
	 * @throws \Aimeos\MW\Media\Exception If image couldn't be saved to the given file name
	 */
	public function save( $filename = null, $mimetype = null )
	{
		if( $mimetype === null ) {
			$mimetype = $this->getMimeType();
		}

		$quality = 90;
		$mime = explode( '/', $mimetype );

		if( isset( $this->options['image']['quality'] ) ) {
			$quality = max( min( (int) $this->options['image']['quality'], 100 ), 0 );
		}

		try
		{
			$this->image->setImageFormat( $mime[1] );
			$this->image->setImageCompressionQuality( $quality );

			if( $filename === null ) {
				return $this->image->getImageBlob();
			}

			$this->image->writeImage( $filename );
		}
		catch( \Exception $e )
		{
			throw new \Aimeos\MW\Media\Exception( $e->getMessage() );
		}
	}


	/**
	 * Scales the image to the given width and height.
	 *
	 * @param integer|null $width New width of the image or null for automatic calculation
	 * @param integer|null $height New height of the image or null for automatic calculation
	 * @param boolean $fit True to keep the width/height ratio of the image
	 * @return \Aimeos\MW\Media\Iface Self object for method chaining
	 */
	public function scale( $width, $height, $fit = true )
	{
		$fit = (bool) $fit;

		try
		{
			if( $fit === true )
			{
				$w = $this->image->getImageWidth();
				$h = $this->image->getImageHeight();
				list( $width, $height ) = $this->getSizeFitted( $w, $h, $width, $height );

				if( $w <= $width && $h <= $height ) {
					return $this;
				}
			}

			$newMedia = clone $this;

			if( $fit === false && $width && $height )
			{
				$newMedia->image->cropThumbnailImage( (int) $width, (int) $height );
				// see https://www.php.net/manual/en/imagick.cropthumbnailimage.php#106710
				$newMedia->image->setImagePage( 0, 0, 0, 0 );
			}
			else
			{
				$newMedia->image->resizeImage( $width, $height, \Imagick::FILTER_CUBIC, 0.8 );
			}

			if( $this->wmimg ) {
				$this->watermark( $this->wmimg );
			}


			return $newMedia;
		}
		catch( \Exception $e )
		{
			throw new \Aimeos\MW\Media\Exception( $e->getMessage() );
		}
	}


	/**
	 * Adds the configured water mark to the image
	 *
	 * @param \Imagick $image Watermark image
	 */
	protected function watermark( \Imagick $image )
	{
		$ww = $image->getImageHeight();
		$wh = $image->getImageWidth();

		if( $ww > $this->getWidth() )
		{
			$wh = $this->getWidth() * $ww / $wh;
			$ww = $this->getWidth();
		}

		if( $wh > $this->getHeight() )
		{
			$ww = $this->getHeight() * $wh / $ww;
			$wh = $this->getHeight();
		}

		$dx = (int) ( $this->getWidth() - $ww ) / 2;
		$dy = (int) ( $this->getHeight() - $wh ) / 2;

		$image->setImageColorspace( $this->image->getImageColorspace() );
		$image->resizeImage( $ww, $wh, \Imagick::FILTER_CUBIC, 0.8 );

		$this->image->compositeImage( $image, \Imagick::COMPOSITE_OVER, $dx, $dy );

		$image->clear();
	}
}
