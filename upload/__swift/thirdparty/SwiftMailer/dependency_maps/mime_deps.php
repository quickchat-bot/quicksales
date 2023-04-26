<?php

require __DIR__.'/../mime_types.php';

SwiftMailer_DependencyContainer::getInstance()
    ->register('properties.charset')
    ->asValue('utf-8')

    ->register('mime.grammar')
    ->asSharedInstanceOf('SwiftMailer_Mime_Grammar')

    ->register('mime.message')
    ->asNewInstanceOf('SwiftMailer_Mime_SimpleMessage')
    ->withDependencies(array(
        'mime.headerset',
        'mime.qpcontentencoder',
        'cache',
        'mime.grammar',
        'properties.charset',
    ))

    ->register('mime.part')
    ->asNewInstanceOf('SwiftMailer_Mime_MimePart')
    ->withDependencies(array(
        'mime.headerset',
        'mime.qpcontentencoder',
        'cache',
        'mime.grammar',
        'properties.charset',
    ))

    ->register('mime.attachment')
    ->asNewInstanceOf('SwiftMailer_Mime_Attachment')
    ->withDependencies(array(
        'mime.headerset',
        'mime.base64contentencoder',
        'cache',
        'mime.grammar',
    ))
    ->addConstructorValue($swiftMailer_mime_types)

    ->register('mime.embeddedfile')
    ->asNewInstanceOf('SwiftMailer_Mime_EmbeddedFile')
    ->withDependencies(array(
        'mime.headerset',
        'mime.base64contentencoder',
        'cache',
        'mime.grammar',
    ))
    ->addConstructorValue($swiftMailer_mime_types)

    ->register('mime.headerfactory')
    ->asNewInstanceOf('SwiftMailer_Mime_SimpleHeaderFactory')
    ->withDependencies(array(
            'mime.qpheaderencoder',
            'mime.rfc2231encoder',
            'mime.grammar',
            'properties.charset',
        ))

    ->register('mime.headerset')
    ->asNewInstanceOf('SwiftMailer_Mime_SimpleHeaderSet')
    ->withDependencies(array('mime.headerfactory', 'properties.charset'))

    ->register('mime.qpheaderencoder')
    ->asNewInstanceOf('SwiftMailer_Mime_HeaderEncoder_QpHeaderEncoder')
    ->withDependencies(array('mime.charstream'))

    ->register('mime.base64headerencoder')
    ->asNewInstanceOf('SwiftMailer_Mime_HeaderEncoder_Base64HeaderEncoder')
    ->withDependencies(array('mime.charstream'))

    ->register('mime.charstream')
    ->asNewInstanceOf('SwiftMailer_CharacterStream_NgCharacterStream')
    ->withDependencies(array('mime.characterreaderfactory', 'properties.charset'))

    ->register('mime.bytecanonicalizer')
    ->asSharedInstanceOf('SwiftMailer_StreamFilters_ByteArrayReplacementFilter')
    ->addConstructorValue(array(array(0x0D, 0x0A), array(0x0D), array(0x0A)))
    ->addConstructorValue(array(array(0x0A), array(0x0A), array(0x0D, 0x0A)))

    ->register('mime.characterreaderfactory')
    ->asSharedInstanceOf('SwiftMailer_CharacterReaderFactory_SimpleCharacterReaderFactory')

    ->register('mime.safeqpcontentencoder')
    ->asNewInstanceOf('SwiftMailer_Mime_ContentEncoder_QpContentEncoder')
    ->withDependencies(array('mime.charstream', 'mime.bytecanonicalizer'))

    ->register('mime.rawcontentencoder')
    ->asNewInstanceOf('SwiftMailer_Mime_ContentEncoder_RawContentEncoder')

    ->register('mime.nativeqpcontentencoder')
    ->withDependencies(array('properties.charset'))
    ->asNewInstanceOf('SwiftMailer_Mime_ContentEncoder_NativeQpContentEncoder')

    ->register('mime.qpcontentencoderproxy')
    ->asNewInstanceOf('SwiftMailer_Mime_ContentEncoder_QpContentEncoderProxy')
    ->withDependencies(array('mime.safeqpcontentencoder', 'mime.nativeqpcontentencoder', 'properties.charset'))

    ->register('mime.7bitcontentencoder')
    ->asNewInstanceOf('SwiftMailer_Mime_ContentEncoder_PlainContentEncoder')
    ->addConstructorValue('7bit')
    ->addConstructorValue(true)

    ->register('mime.8bitcontentencoder')
    ->asNewInstanceOf('SwiftMailer_Mime_ContentEncoder_PlainContentEncoder')
    ->addConstructorValue('8bit')
    ->addConstructorValue(true)

    ->register('mime.base64contentencoder')
    ->asSharedInstanceOf('SwiftMailer_Mime_ContentEncoder_Base64ContentEncoder')

    ->register('mime.rfc2231encoder')
    ->asNewInstanceOf('SwiftMailer_Encoder_Rfc2231Encoder')
    ->withDependencies(array('mime.charstream'))

    // As of PHP 5.4.7, the quoted_printable_encode() function behaves correctly.
    // see https://github.com/php/php-src/commit/18bb426587d62f93c54c40bf8535eb8416603629
    ->register('mime.qpcontentencoder')
    ->asAliasOf(PHP_VERSION_ID >= 50407 ? 'mime.qpcontentencoderproxy' : 'mime.safeqpcontentencoder')
;

unset($swiftMailer_mime_types);
