<?php

namespace spaf\simputils\models;


use spaf\simputils\components\SimpleObject;
use spaf\simputils\exceptions\GitDirectoryNotFound;
use spaf\simputils\helpers\DateTimeHelper;

/**
 * @codeCoverageIgnore
 */
class GitRepo extends SimpleObject {

	protected ?string $path = null;

	/**
	 * @throws GitDirectoryNotFound
	 */
	public function __construct(string $path) {
		if (file_exists($path))
			$this->path = $path;
		if (!$this->gitDirCheck($path))
			throw new GitDirectoryNotFound('Specified dir is not GIT');
	}

	protected function gitDirCheck($path): bool {
		if (file_exists($path)) {
			$res = `git -C {$path} rev-parse --git-dir 2> /dev/null`;
			return !empty($res);
		}

		return false;
	}

	protected function clearString($str): array|string|null {
		return preg_replace('/[\n\t]*/', '', $str);
	}

	//	Branch name:				git rev-parse --abbrev-ref HEAD
	public function getBranch($commit_id = 'HEAD'): string {
		return $this->clearString(`git -C "{$this->path}" name-rev {$commit_id} --name-only`);
	}

	//  Get commit id:				git rev-parse HEAD
	public function getCommitId($branch_name = 'HEAD'): string {
		return $this->clearString(`git -C "{$this->path}" rev-parse {$branch_name}`);
	}

	//  Get short id:				git show --format="%h" --no-patch
	public function getCommitIdShort($branch_name = 'HEAD'): string {
		return $this->clearString($this->show('%h', $branch_name));
	}

	public function show($fmt, $ref = 'HEAD', $is_no_patch = true): string {
		$no_patch = $is_no_patch?'--no-patch':'';
		return `git -C "{$this->path}" show --format="{$fmt}" {$ref} {$no_patch}`;
	}

	//  Get author:					git show --format="%an" --no-patch
	public function getAuthorName($ref = 'HEAD'): string {
		return $this->clearString($this->show('%an', $ref));
	}

	//  Get email:					git show --format="%ae" --no-patch
	public function getAuthorEmail($ref = 'HEAD'): string {
		return $this->clearString($this->show('%ae', $ref));
	}

	//  Get timestamp:				git show --format="%at" --no-patch
	public function getAuthorDatetime($ref = 'HEAD'): ?DateTime {
		$ts = $this->show('%at', $ref);
		return !empty($ts)?DateTimeHelper::normalize((int) $ts):null;
	}

	//  Get committer:				git show --format="%cn" --no-patch
	public function getCommitterName($ref = 'HEAD'): string {
		return $this->clearString($this->show('%cn', $ref));
	}

	//  Get committer email:		git show --format="%ce" --no-patch
	public function getCommitterEmail($ref = 'HEAD'): string {
		return $this->clearString($this->show('%ce', $ref));
	}

	//  Get committer datetime:		git show --format="%cI" --no-patch
	public function getCommitterDatetime($ref = 'HEAD'): ?DateTime {
		$ts = $this->show('%ct', $ref);
		return !empty($ts)?DateTimeHelper::normalize((int) $ts):null;
	}

	//  Subject:					git show --format="%s" --no-patch
	public function getCommitSubject($ref = 'HEAD'): string {
		return $this->clearString($this->show('%s', $ref));
	}

	//  Body:						git show --format="%b" --no-patch
	public function getCommitBody($ref = 'HEAD'): string {
		return $this->show('%b', $ref);
	}

}