<?php

namespace spaf\simputils\models;


use spaf\simputils\generic\BasicSystemFingerprint;
use spaf\simputils\traits\DefaultSystemFingerprintTrait;

/**
 * Component for system fingerprint collection, storage and comparison
 *
 * System fingerprint - is a special "textually-represented" hashing tactics to determine, whether
 * it's the "same/similar" system or not (clustered docker containers - is a very good example).
 *
 * For example - if you want to store something in the common storage or cache, and you have
 * multiple instances of the same or a similar app that should share info between
 * requests/each other, then the stored fingerprint together with data can determine, whether
 * the app should use the data, or generate new ones.
 *
 * In simple words - system fingerprint, it's a hash of some set of PhpInfo fields. In case of
 * strict mode - more fields added to the cache, and otherwise some basic fields are added to
 * the hash.
 *
 * The fields set may vary to the version of the framework, this is backwards compatibility
 * mechanism that must help with easier changing set of fields for newer versions.
 *
 * Framework version is being part of the fingerprint, and the framework has to store all
 * the versions (in case of unlikely severe growth of the versions modifications of the required
 * fields - it might be extracted to the independent sub-library, but compatibility must be
 * preserved, always!)
 *
 * **Important:** Here provided build type and build revision will be ignored, so "1.0.0" and
 * "1.0.0RC2" would both mean "1.0.0". So only semantic versioning major, minor and patch
 * playing role in it.
 *
 * Simple usage example
 * ```php
 *  $sf = new SystemFingerprint();
 *  echo $sf;
 * ```
 *
 * Output would be something like (one line without break):
 * ```
 * DSF/9ad2f41fc1e44450714bad01764ff7d9,4b01641228e44a8be26d0071c3f7bb0e94800217753d5034c4b00986cd38
 * 1f22/0.2.3/0
 * ```
 * This is non-strict fingerprint of the system code was ran on. In your case value will be
 * different.
 *
 * "Strict" usage example
 * ```php
 *  $sf = new SystemFingerprint(strict: true);
 *  echo $sf;
 * ```
 *
 * Output would be something like (one line without break):
 * ```
 * 0.2.3/ae25409525d29f9ecb3e8c2d574b3f70,23dacaf3e0fbe720d0ccb517ea0494d8a28cbf9dc2338499
 * 597aca118ab0200e/S
 * ```
 * This is strict fingerprint of the system code was ran on. In your case value will be
 * different.
 *
 * And **important to note that the value for strict fingerprint is more volatile,
 * so if you will change anything in PHP ini files/configuration - you will get different values
 * of fingerprint**. Even the CLI and WEB runtimes can have different fingerprints
 *
 * **Important:** It's strongly recommended to use out of the box functionality when possible,
 * so when the newer version of framework has better functionality - it would transparently
 * uses the newer functionality, and stored older fingerprints will be validating independently.
 * (If you have your own infrastructure for the fingerprinting implemented instead of
 * the default one, then ignore this important message, because it's not applicable to your case.)
 *
 * Different version usage:
 * ```php
 *
 *  // Keep in mind "1.0.1RC" and "1.0.1" will be considered the same because build type
 *  // and revision are being ignored. Only major, minor and patch are relevant.
 *  $sf = new SystemFingerprint('1.0.1RC3);
 *  // or
 *  $sf = new SystemFingerprint('1.0.1);
 *  echo $sf;
 * ```
 * Output would be something like (both of them logically are the same):
 * ```
 *  1.0.1/828bcef8763c1bc616e25a06be4b90ff,3e80b3778b3b03766e7be993131c0af2ad05630c5d96fb7
 *  fa132d05b77336e04/
 * ```
 *
 *
 * @todo Important: The usage of the caching/storing mechanisms like "redis" or similar is not
 *       implemented yet. So this is for general usage for now, but in the future will be integrated
 *       into the caching/storage mechanisms.
 *
 * @todo implement "Explain" functionality
 *
 * FIX  Component is incomplete! (Architecturally and strictness level functionality)
 * FIX  Adapt documentation after completing the code.
 *
 * @property-read $parts
 * @property-read $name
 * @property-read $data
 */
class SystemFingerprint extends BasicSystemFingerprint {
	use DefaultSystemFingerprintTrait;

	const NAME = 'DSF';

}
