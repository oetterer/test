<?php
/**
 * Contains the class for replacing image normal image display with a modal.
 *
 * @copyright (C) 2018, Tobias Oetterer, University of Paderborn
 * @license       https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 (or later)
 *
 * This file is part of the MediaWiki extension BootstrapComponents.
 * The BootstrapComponents extension is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * The BootstrapComponents extension is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @file
 * @ingroup       BootstrapComponents
 * @author        Tobias Oetterer
 */

namespace BootstrapComponents;

use \Linker;
use \Html;
use \MediaWiki\MediaWikiServices;
use \RequestContext;
use \Title;

/**
 * Class ImageModal
 *
 * @since 1.0
 */
class ImageModal implements NestableInterface {
	/**
	 * @var \DummyLinker $dummyLinker
	 */
	private $dummyLinker;

	/**
	 * @var \File $file
	 */
	private $file;

	/**
	 * @var string $id
	 */
	private $id;

	/**
	 * @var NestingController $nestingController
	 */
	private $nestingController;

	/**
	 * @var NestableInterface|false $parentComponent
	 */
	private $parentComponent;

	/**
	 * @var ParserOutputHelper $parserOutputHelper
	 */
	private $parserOutputHelper;

	/**
	 * @var bool $disableSourceLink
	 */
	private $disableSourceLink;

	/**
	 * @var Title $title
	 */
	private $title;

	/**
	 * ImageModal constructor.
	 *
	 * @param \DummyLinker       $dummyLinker
	 * @param \Title             $title
	 * @param \File              $file
	 * @param NestingController  $nestingController
	 * @param ParserOutputHelper $parserOutputHelper DI for unit testing
	 *
	 * @throws \MWException cascading {@see \BootstrapComponents\ApplicationFactory} methods
	 */
	public function __construct( $dummyLinker, $title, $file, $nestingController = null, $parserOutputHelper = null ) {
		$this->file = $file;
		$this->dummyLinker = $dummyLinker;
		$this->title = $title;

		$this->nestingController = is_null( $nestingController )
			? ApplicationFactory::getInstance()->getNestingController()
			: $nestingController;
		$this->parserOutputHelper = is_null( $parserOutputHelper )
			? ApplicationFactory::getInstance()->getParserOutputHelper()
			: $parserOutputHelper ;

		$this->parentComponent = $this->getNestingController()->getCurrentElement();
		$this->id = $this->getNestingController()->generateUniqueId(
			$this->getComponentName()
		);
		$this->disableSourceLink = false;
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentName() {
		return "image_modal";
	}

	/**
	 * @inheritdoc
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param array       $frameParams   Associative array of parameters external to the media handler.
	 *                                   Boolean parameters are indicated by presence or absence, the value is arbitrary and
	 *                                   will often be false.
	 *                                   thumbnail       If present, downscale and frame
	 *                                   manualthumb     Image name to use as a thumbnail, instead of automatic scaling
	 *                                   framed          Shows image in original size in a frame
	 *                                   frameless       Downscale but don't frame
	 *                                   upright         If present, tweak default sizes for portrait orientation
	 *                                   upright_factor  Fudge factor for "upright" tweak (default 0.75)
	 *                                   border          If present, show a border around the image
	 *                                   align           Horizontal alignment (left, right, center, none)
	 *                                   valign          Vertical alignment (baseline, sub, super, top, text-top, middle,
	 *                                   bottom, text-bottom)
	 *                                   alt             Alternate text for image (i.e. alt attribute). Plain text.
	 *                                   class           HTML for image classes. Plain text.
	 *                                   caption         HTML for image caption.
	 *                                   link-url        URL to link to
	 *                                   link-title      Title object to link to
	 *                                   link-target     Value for the target attribute, only with link-url
	 *                                   no-link         Boolean, suppress description link
	 * @param array       $handlerParams Associative array of media handler parameters, to be passed
	 *                                   to transform(). Typical keys are "width" and "page".
	 * @param string|bool $time          Timestamp of the file, set as false for current
	 * @param string      $res           Final HTML output, used if this returns false
	 *
	 * @throws \MWException     cascading {@see \BootstrapComponents\NestingController::open}
	 * @throws \ConfigException cascading {@see \BootstrapComponents\ImageModal::generateTrigger}
	 *
	 * @return bool
	 */
	public function parse( &$frameParams, &$handlerParams, &$time, &$res ) {
		if ( !$this->assessResponsibility( $this->getFile(), $frameParams ) ) {
			return true;
		}

		// it's on us, let's do some modal-ing
		$this->augmentParserOutput();
		$this->getNestingController()->open( $this );

		$sanitizedFrameParams = $this->sanitizeFrameParams( $frameParams );
		$handlerParams['page'] = isset( $handlerParams['page'] ) ? $handlerParams['page'] : false;

		$trigger = $this->generateTrigger(
			$this->getFile(),
			$sanitizedFrameParams,
			$handlerParams
		);
		if ( $trigger === false ) {
			// something wrong with the trigger. Relegating back
			return true;
		}

		list ( $content, $largeDialog ) = $this->generateContent(
			$this->getFile(),
			$sanitizedFrameParams,
			$handlerParams
		);

		if ( $content === false ) {
			// could not create content image. Relegating back
			return true;
		}

		$modal = ApplicationFactory::getInstance()->getModalBuilder(
			$this->getId(),
			$trigger,
			$content
		);
		$modal->setHeader(
			$this->getTitle()->getBaseText()
		);

		if ( !$this->disableSourceLink ) {
			$modal->setFooter(
				$this->generateButtonToSource(
					$this->getTitle(),
					$handlerParams
				)
			);
		};

		if ( $largeDialog ) {
			$modal->setDialogClass( 'modal-lg' );
		}

		$res = $modal->parse();

		$this->getNestingController()->close(
			$this->getId()
		);

		return false;
	}

	/**
	 * This is public, so it can be unit tested directly, making it possible to draw a direct comparison between this and
	 * {@see \Linker::makeImageLink}
	 *
	 * @param \File $file
	 * @param array $sanitizedFrameParams
	 * @param array $handlerParams
	 *
	 * @throws \ConfigException cascading {@see \BootstrapComponents\ImageModal::generateTriggerCreateThumb}
	 *
	 * @return false|string
	 */
	public function generateTrigger( $file, $sanitizedFrameParams, $handlerParams ) {
		/** @var \MediaTransformOutput $thumb */
		list( $thumb, $thumbHandlerParams ) = $this->generateTriggerCreateThumb( $file, $sanitizedFrameParams, $handlerParams );

		if ( !$thumb ) {
			// We could deal with an invalid thumb, but then we would also need to signal in invalid modal.
			// Better let Linker.php take care
			wfDebugLog( 'BootstrapComponents', 'Image modal encountered an invalid thumbnail. Relegating back.' );
			return false;
		}
		$triggerOptions = $this->generateTriggerCalculateHtmlOptions(
			$file,
			$thumb,
			$sanitizedFrameParams,
			$thumbHandlerParams
		);
		$publicationString = $thumb->toHtml( $triggerOptions );
		return $this->generateTriggerWrapAndFinalize(
			$publicationString,
			$sanitizedFrameParams,
			$thumb->getWidth()
		);
	}

	/**
	 * After this, all bool params ( 'thumbnail', 'framed', 'frameless', 'border' ) are true, if they were present before, false otherwise and all
	 * string params are set (to the original value or the empty string).
	 *
	 * This method is public, because it is used in {@see \BootstrapComponents\Tests\ImageModalTest::doTestCompareTriggerWithOriginalThumb}
	 *
	 * @param array $frameParams
	 *
	 * @return array
	 */
	public function sanitizeFrameParams( $frameParams ) {
		foreach ( [ 'thumbnail', 'framed', 'frameless', 'border' ] as $boolField ) {
			$frameParams[$boolField] = isset( $frameParams[$boolField] );
		}
		foreach ( [ 'align', 'alt', 'caption', 'class', 'title' ] as $stringField ) {
			$frameParams[$stringField] = isset( $frameParams[$stringField] ) ? $frameParams[$stringField] : '';
		}
		$frameParams['caption'] = $this->preventModalInception( $frameParams['caption'] );
		$frameParams['title'] = $this->preventModalInception( $frameParams['title'] );
		return $frameParams;
	}

	/**
	 * Disables the source link in modal content.
	 */
	public function disableSourceLink() {
		$this->disableSourceLink = true;
	}

	/**
	 * Runs various tests, to see, if we delegate processing back to {@see \Linker::makeImageLink}
	 * After this, we can assume:
	 * * file is a {@see \File} and exists
	 * * there is no link param set (link-url, link-title, link-target, no-link)
	 * * we are not inside an image modal (thanks to {@see \BootstrapComponents\ImageModal::getNestingController})
	 * * file allows inline display (ref {@see \File::allowInlineDisplay})
	 *
	 * @param \File $file
	 * @param array $frameParams
	 *
	 * @return bool true, if all assertions hold, false if one fails (see above)
	 */
	protected function assessResponsibility( $file, $frameParams ) {
		if ( !$file || !$file->exists() ) {
			wfDebugLog( 'BootstrapComponents', 'Image modal encountered an invalid file. Relegating back.' );
			return false;
		}
		if ( isset( $frameParams['link-url'] ) || isset( $frameParams['link-title'] )
			|| isset( $frameParams['link-target'] ) || isset( $frameParams['no-link'] )
		) {
			wfDebugLog( 'BootstrapComponents', 'Image modal detected link options. Relegating back.' );
			return false;
		}
		if ( $this->getParentComponent() && $this->getParentComponent()->getComponentName() == $this->getComponentName() ) {
			// there cannot be an image modal inside an image modal
			// never should occur, but better safe than sorry
			return false;
		}
		if ( !$file->allowInlineDisplay() ) {
			// let Linker.php handle these cases as well
			return false;
		}
		return true;
	}

	/**
	 * @param \File $file
	 * @param array $sanitizedFrameParams
	 * @param array $handlerParams
	 *
	 * @return array bool|string bool (large image yes or no)
	 */
	protected function generateContent( $file, $sanitizedFrameParams, $handlerParams ) {

		$img = $file->getUnscaledThumb(
			[ 'page' => $handlerParams['page'] ]
		);
		if ( !$img ) {
			return [ false, false ];
		}
		$imgParams = [
			'alt'       => $sanitizedFrameParams['alt'],
			'title'     => $sanitizedFrameParams['title'],
			'img-class' => trim( $sanitizedFrameParams['class'] . ' img-responsive' ),
		];
		$imgString = $img->toHtml( $imgParams );
		if ( $sanitizedFrameParams['caption'] ) {
			$imgString .= ' ' . Html::rawElement(
					'div',
					[ 'class' => 'modal-caption' ],
					$sanitizedFrameParams['caption']
				);
		}
		return [ $imgString, $img->getWidth() > 600 ];
	}

	/**
	 * @return string
	 */
	protected function generateTriggerBuildZoomIcon() {
		return Html::rawElement(
			'div',
			[
				'class' => 'magnify',
			],
			Html::rawElement(
				'a',
				[
					'class' => 'internal',
					'title' => wfMessage( 'thumbnail-more' )->text(),
				],
				""
			)
		);
	}

	/**
	 * @param \File                 $file
	 * @param \MediaTransformOutput $thumb
	 * @param array                 $sanitizedFrameParams
	 * @param array                 $thumbHandlerParams
	 *
	 * @return array
	 */
	protected function generateTriggerCalculateHtmlOptions( $file, $thumb, $sanitizedFrameParams, $thumbHandlerParams ) {
		if ( $sanitizedFrameParams['thumbnail'] && (!isset( $sanitizedFrameParams['manualthumb'] ) && !$sanitizedFrameParams['framed']) ) {
			Linker::processResponsiveImages( $file, $thumb, $thumbHandlerParams );
		}
		$options = [
			'alt'       => $sanitizedFrameParams['alt'],
			'title'     => $sanitizedFrameParams['title'],
			'img-class' => $sanitizedFrameParams['class'] . ' img-responsive',
		];
		if ( $sanitizedFrameParams['thumbnail'] || isset( $sanitizedFrameParams['manualthumb'] ) || $sanitizedFrameParams['framed'] ) {
			$options['img-class'] .= ' thumbimage';
		} elseif ( $sanitizedFrameParams['border'] ) {
			$options['img-class'] .= ' thumbborder';
		}
		$options['img-class'] = trim( $options['img-class'] );

		// in Linker.php, options also run through {@see \Linker::getImageLinkMTOParams} to calculate the link value.
		// Since we abort at the beginning, if any link related frameParam is set, we can skip this.
		// also, obviously, we don't want to have ANY link around the img present.

		return $options;
	}

	/**
	 * @param \File $file
	 * @param array $sanitizedFrameParams
	 * @param array $handlerParams
	 *
	 * @throws \ConfigException cascading {@see \BootstrapComponents\ImageModal::generateTriggerReevaluateImageDimensions}
	 *
	 * @return array [ \MediaTransformOutput|false, handlerParams ]
	 */
	protected function generateTriggerCreateThumb( $file, $sanitizedFrameParams, $handlerParams ) {
		$transform = !isset( $sanitizedFrameParams['manualthumb'] ) && !$sanitizedFrameParams['framed'];
		$thumbFile = $file;
		$thumbHandlerParams = $this->generateTriggerReevaluateImageDimensions( $file, $sanitizedFrameParams, $handlerParams );

		if ( isset( $sanitizedFrameParams['manualthumb'] ) ) {
			$thumbFile = $this->getFileFromTitle( $sanitizedFrameParams['manualthumb'] );
		}

		if ( !$thumbFile
			|| (!$sanitizedFrameParams['thumbnail'] && !$sanitizedFrameParams['framed'] && !isset( $thumbHandlerParams['width'] ))
		) {
			return [ false, $thumbHandlerParams ];
		}

		if ( $transform ) {
			return [ $thumbFile->transform( $thumbHandlerParams ), $thumbHandlerParams ];
		} else {
			return [ $thumbFile->getUnscaledThumb( $thumbHandlerParams ), $thumbHandlerParams ];
		}
	}

	/**
	 * This is mostly taken from {@see \Linker::makeImageLink}, rest originates from {@see \Linker::makeThumbLink2}. Extracts are heavily
	 * squashed and condensed
	 *
	 * @param \File $file
	 * @param array $sanitizedFrameParams
	 * @param array $handlerParams
	 *
	 * @throws \ConfigException cascading {@see \BootstrapComponents\ImageModal::generateTriggerCalculateImageWidth}
	 *
	 * @return array thumbnail handler params
	 */
	protected function generateTriggerReevaluateImageDimensions( $file, $sanitizedFrameParams, $handlerParams ) {
		if ( !isset( $handlerParams['width'] ) ) {
			$handlerParams = $this->generateTriggerCalculateImageWidth( $file, $sanitizedFrameParams, $handlerParams );
		}
		if ( $this->amIThumbnailRelated( $sanitizedFrameParams ) ) {
			if ( empty( $handlerParams['width'] ) && !$sanitizedFrameParams['frameless'] ) {
				// Reduce width for upright images when parameter 'upright' is used
				$handlerParams['width'] = isset( $sanitizedFrameParams['upright'] ) ? 130 : 180;
			}
			if ( $sanitizedFrameParams['frameless']
				|| (!isset( $sanitizedFrameParams['manualthumb'] ) && !$sanitizedFrameParams['framed'])
			) {
				# Do not present an image bigger than the source, for bitmap-style images
				# This is a hack to maintain compatibility with arbitrary pre-1.10 behavior
				# For "frameless" option: do not present an image bigger than the
				# source (for bitmap-style images). This is the same behavior as the
				# "thumb" option does it already.
				$srcWidth = $file->getWidth( $handlerParams['page'] );
				if ( $srcWidth && !$file->mustRender() && $handlerParams['width'] > $srcWidth ) {
					$handlerParams['width'] = $srcWidth;
				}
			}
		}

		return $handlerParams;
	}

	/**
	 * Calculates a with from File, $sanitizedFrameParams, and $handlerParams
	 *
	 * @param \File $file
	 * @param array $sanitizedFrameParams
	 * @param array $handlerParams
	 *
	 * @throws \ConfigException cascading {@see \Config::get}
	 *
	 * @return array thumbnail handler params
	 */
	protected function generateTriggerCalculateImageWidth( $file, $sanitizedFrameParams, $handlerParams ) {
		$globalConfig = MediaWikiServices::getInstance()->getMainConfig();
		if ( isset( $handlerParams['height'] ) && $file->isVectorized() ) {
			// If its a vector image, and user only specifies height
			// we don't want it to be limited by its "normal" width.
			$handlerParams['width'] = $globalConfig->get( 'SVGMaxSize' );
		} else {
			$handlerParams['width'] = $file->getWidth( $handlerParams['page'] );
		}

		if ( $this->amIThumbnailRelated( $sanitizedFrameParams ) || !$handlerParams['width'] ) {
			$thumbLimits = $globalConfig->get( 'ThumbLimits' );
			$widthOption = $this->generateTriggerGetWidthOption( $thumbLimits );

			// Reduce width for upright images when parameter 'upright' is used
			if ( isset( $sanitizedFrameParams['upright'] ) && $sanitizedFrameParams['upright'] == 0 ) {
				$sanitizedFrameParams['upright'] = $globalConfig->get( 'ThumbUpright' );
			}

			// For caching health: If width scaled down due to upright
			// parameter, round to full __0 pixel to avoid the creation of a
			// lot of odd thumbs.
			$prefWidth = isset( $sanitizedFrameParams['upright'] )
				? round( $thumbLimits[$widthOption] * $sanitizedFrameParams['upright'], -1 )
				: $thumbLimits[$widthOption];

			// Use width which is smaller: real image width or user preference width
			// Unless image is scalable vector.
			if ( !isset( $handlerParams['height'] ) && ($handlerParams['width'] <= 0 ||
					$prefWidth < $handlerParams['width'] || $file->isVectorized()) ) {
				$handlerParams['width'] = $prefWidth;
			}
		}
		return $handlerParams;
	}

	/**
	 * @param array $thumbLimits
	 *
	 * @return int|string
	 */
	protected function generateTriggerGetWidthOption( $thumbLimits ) {

		$user = RequestContext::getMain()->getUser();
		$widthOption = $user::getDefaultOption( 'thumbsize' );

		// we have a problem here: the original \Linker::makeImageLink does get a value for $widthOption,
		// for instance in parser tests. unfortunately, this value is not passed through the hook.
		// so there are instances, there $thumbLimits[$widthOption] is not defined.
		// solution: we cheat and take the first one
		if ( $widthOption !== null && isset( $thumbLimits[$widthOption] ) ) {
			return $widthOption;
		}
		$availableOptions = array_keys( $thumbLimits );
		return reset( $availableOptions );
	}

	/**
	 * Envelops the thumbnail img-tag.
	 *
	 * @param string $thumbString
	 * @param array  $sanitizedFrameParams
	 * @param int    $thumbWidth
	 *
	 * @return string
	 */
	protected function generateTriggerWrapAndFinalize( $thumbString, $sanitizedFrameParams, $thumbWidth ) {
		if ( $sanitizedFrameParams['thumbnail'] || isset( $sanitizedFrameParams['manualthumb'] ) || $sanitizedFrameParams['framed'] ) {
			if ( !strlen( $sanitizedFrameParams['align'] ) ) {
				$sanitizedFrameParams['align'] = $this->getTitle()->getPageLanguage()->alignEnd();
			}

			$zoomIcon = !$sanitizedFrameParams['framed'] ? $this->generateTriggerBuildZoomIcon() : '';
			$outerWidth = $thumbWidth + 2;

			$ret = Html::rawElement(
				'div',
				[
					'class' => 'thumb t' . ($sanitizedFrameParams['align'] == 'center' ? 'none' : $sanitizedFrameParams['align']),
				],
				ModalBuilder::wrapTriggerElement(
					Html::rawElement(
						'div',
						[
							'class' => 'thumbinner',
							'style' => 'width:' . $outerWidth . 'px;',
						],
						$thumbString . '  ' . Html::rawElement(
							'div',
							[ 'class' => 'thumbcaption' ],
							$zoomIcon . $sanitizedFrameParams['caption']
						)
					),
					$this->getId()
				)
			);
		} elseif ( strlen( $sanitizedFrameParams['align'] ) && $sanitizedFrameParams['align'] != 'center' ) {
			$ret = Html::rawElement(
				'div',
				[ 'class' => 'float' . $sanitizedFrameParams['align'], ],
				ModalBuilder::wrapTriggerElement( $thumbString, $this->getId() )
			);
		} elseif ( $sanitizedFrameParams['align'] == 'center' ) {
			$ret = Html::rawElement(
				'div',
				[ 'class' => 'center', ],
				ModalBuilder::wrapTriggerElement( $thumbString, $this->getId() )
			);
		} else {
			$ret = ModalBuilder::wrapTriggerElement( $thumbString, $this->getId() );
		}

		return str_replace( "\n", ' ', $ret );
	}

	/**
	 * @return \DummyLinker
	 */
	/** @scrutinizer ignore-unused */
	protected function getDummyLinker() {
		return $this->dummyLinker;
	}

	/**
	 * @return \File
	 */
	protected function getFile() {
		return $this->file;
	}

	/**
	 * @return NestingController
	 */
	protected function getNestingController() {
		return $this->nestingController;
	}

	/**
	 * @return null|NestableInterface
	 */
	protected function getParentComponent() {
		return $this->parentComponent;
	}

	/**
	 * @return ParserOutputHelper
	 */
	protected function getParserOutputHelper() {
		return $this->parserOutputHelper;
	}

	/**
	 * @return Title
	 */
	protected function getTitle() {
		return $this->title;
	}

	/**
	 * @param $sanitizedFrameParams
	 *
	 * @return bool
	 */
	private function amIThumbnailRelated( $sanitizedFrameParams ) {
		return $sanitizedFrameParams['thumbnail']
			|| isset( $sanitizedFrameParams['manualthumb'] )
			|| $sanitizedFrameParams['framed']
			|| $sanitizedFrameParams['frameless'];
	}

	/**
	 * Performs all the mandatory actions on the parser output for the component class
	 *
	 * @throws \MWException cascading {@see \BootstrapComponents\ApplicationFactory::getComponentLibrary}
	 */
	private function augmentParserOutput() {
		$skin = $this->getParserOutputHelper()->getNameOfActiveSkin();
		$this->getParserOutputHelper()->loadBootstrapModules();
		$this->getParserOutputHelper()->addModules(
			ApplicationFactory::getInstance()->getComponentLibrary()->getModulesFor( 'modal', $skin )
		);
	}

	/**
	 * @param string $fileTitle
	 *
	 * @return bool|\File
	 */
	private function getFileFromTitle( $fileTitle ) {
		$manual_title = Title::makeTitleSafe( NS_FILE, $fileTitle );
		if ( $manual_title ) {
			return wfFindFile( $manual_title );
		}
		return false;
	}

	/**
	 * @param Title $title
	 * @param array $handlerParams
	 *
	 * @return string
	 */
	private function generateButtonToSource( $title, $handlerParams ) {
		$url = $title->getLocalURL();
		if ( isset( $handlerParams['page'] ) ) {
			$url = wfAppendQuery( $url, [ 'page' => $handlerParams['page'] ] );
		}
		return Html::rawElement(
			'a',
			[
				'class' => 'btn btn-primary',
				'role'  => 'button',
				'href'  => $url,
			],
			wfMessage( 'bootstrap-components-image-modal-source-button' )->inContentLanguage()->text()
		);
	}

	/**
	 * We don't want a modal inside a modal. Unfortunately, the caption (and title) are parsed, before the modal is generated. So instead of
	 * building the modal from the outside, it is build from the inside. This method tries to detect this construct and removes any modal from
	 * the supplied text and replaces it with the image tag found inside the modal caption content.
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	private function preventModalInception( $text ) {
		if ( preg_match(
			'~div class="modal-dialog.+div class="modal-content.+div class="modal-body.+'
			. '(<img[^>]*/>).+ class="modal-footer.+~Ds', $text, $matches ) ) {
			$text = $matches[1];
		}
		return $text;
	}
}