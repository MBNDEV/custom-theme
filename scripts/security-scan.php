#!/usr/bin/env php
<?php
/**
 * Security scan: detect possible token/credential leaks in theme code.
 * Scans PHP and config-like files; skips vendor, node_modules, build, and lockfiles.
 * Run via: composer run lint:security
 *
 * @package CustomTheme
 */

$themeRoot = dirname( __DIR__ );

/**
 * Directory names to never recurse into (fast; avoids scanning node_modules, etc.).
 *
 * @var array<int, string>
 */
$skipDirNames = array(
	'node_modules',
	'vendor',
	'build',
	'.git',
	'classnames',
);

/**
 * Large dependency manifests — not worth scanning line-by-line for secrets.
 *
 * @var array<int, string>
 */
$skipBasenames = array(
	'package-lock.json',
	'composer.lock',
);

$extensions = array( 'php', 'env', 'ini', 'json', 'yaml', 'yml' );

$patterns = array(
	'AWS access key'          => '/\bAKIA[0-9A-Z]{16}\b/',
	'GitHub token (ghp_)'    => '/\bghp_[a-zA-Z0-9]{36,}\b/',
	'GitHub token (gho_)'    => '/\bgho_[a-zA-Z0-9]+\b/',
	'GitHub PAT'             => '/\bgithub_pat_[a-zA-Z0-9_]+\b/',
	'Stripe secret key'      => '/\bsk_(live|test)_[a-zA-Z0-9]{24,}\b/',
	'Stripe publishable'     => '/\bpk_(live|test)_[a-zA-Z0-9]{24,}\b/',
	'Private key in code'    => '/-----BEGIN\s+(?:RSA\s+)?PRIVATE KEY-----/',
	'Bearer token in string' => '/[\'"]Bearer\s+[a-zA-Z0-9._-]{20,}[\'"]/',
	'Hardcoded password ='   => '/password\s*=\s*[\'"][^\'"]{8,}[\'"]/i',
	'Hardcoded api_key ='    => '/api_?key\s*=\s*[\'"][^\'"]{16,}[\'"]/i',
	'Hardcoded secret ='     => '/\bsecret\s*=\s*[\'"][^\'"]{10,}[\'"]/i',
	'Hardcoded token ='      => '/\btoken\s*=\s*[\'"][^\'"]{16,}[\'"]/i',
);

$placeholderHints = array(
	'your_', 'replace_me', 'xxx', 'example.com', 'placeholder',
	'<insert', 'TODO', 'FIXME', 'changeme', 'secret_here', 'key_here',
);

$dirIterator = new RecursiveDirectoryIterator( $themeRoot, RecursiveDirectoryIterator::SKIP_DOTS );
$filter      = new RecursiveCallbackFilterIterator(
	$dirIterator,
	function ( $current ) use ( $skipDirNames ) {
		if ( ! $current->isDir() ) {
			return true;
		}
		return ! in_array( $current->getFilename(), $skipDirNames, true );
	}
);

$iter   = new RecursiveIteratorIterator( $filter );
$issues = array();

foreach ( $iter as $file ) {
	if ( ! $file->isFile() ) {
		continue;
	}
	$path = $file->getPathname();
	$rel  = str_replace( $themeRoot . DIRECTORY_SEPARATOR, '', $path );
	$ext  = strtolower( $file->getExtension() );

	if ( in_array( $file->getFilename(), $skipBasenames, true ) ) {
		continue;
	}
	if ( ! in_array( $ext, $extensions, true ) ) {
		continue;
	}

	$content = @file_get_contents( $path );
	if ( false === $content ) {
		continue;
	}

	$lines = explode( "\n", $content );
	foreach ( $lines as $num => $line ) {
		$lineNum = $num + 1;
		foreach ( $patterns as $name => $regex ) {
			if ( ! preg_match( $regex, $line ) ) {
				continue;
			}
			$isPlaceholder = false;
			foreach ( $placeholderHints as $hint ) {
				if ( stripos( $line, $hint ) !== false ) {
					$isPlaceholder = true;
					break;
				}
			}
			if ( $isPlaceholder ) {
				continue;
			}
			$issues[] = array(
				'file'    => $rel,
				'line'    => $lineNum,
				'rule'    => $name,
				'snippet' => trim( substr( $line, 0, 100 ) ) . ( strlen( $line ) > 100 ? '...' : '' ),
			);
		}
	}
}

if ( count( $issues ) === 0 ) {
	echo "Security scan: no token/credential patterns found.\n";
	exit( 0 );
}

fwrite( STDERR, 'Security scan found ' . count( $issues ) . " possible secret(s):\n" );
foreach ( $issues as $i ) {
	fwrite( STDERR, sprintf( "  %s:%d [%s]\n    %s\n", $i['file'], $i['line'], $i['rule'], $i['snippet'] ) );
}
fwrite( STDERR, "Remove or replace with env/config and re-run.\n" );
exit( 1 );
