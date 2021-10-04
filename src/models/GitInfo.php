<?php

namespace spaf\simputils\models;


use spaf\simputils\components\SimpleObject;
use spaf\simputils\exceptions\GitDirectoryNotFound;
use spaf\simputils\helpers\DateTimeHelper;

class GitInfo extends SimpleObject {

	protected ?string $path = null;

	/**
	 * @throws GitDirectoryNotFound
	 */
	public function __construct(string $path) {
		if (file_exists($path))
			$this->path = $path;
		if (!$this->git_dir_check($path))
			throw new GitDirectoryNotFound('Specified dir is not GIT');
	}

	protected function git_dir_check($path): bool {
		if (file_exists($path)) {
			$res = `git -C {$path} rev-parse --git-dir 2> /dev/null`;
			return !empty($res);
		}

		return false;
	}

	/**
	 * @throws GitDirectoryNotFound
	 */
	public static function wrap(string $path) {
		return new static($path);
	}

	protected function clear_string($str): array|string|null {
		return preg_replace('/[\n\t]*/', '', $str);
	}

	//	Branch name:				git rev-parse --abbrev-ref HEAD
	public function get_branch($commit_id = 'HEAD'): string {
		return $this->clear_string(`git -C "{$this->path}" name-rev {$commit_id} --name-only`);
	}

	//  Get commit id:				git rev-parse HEAD
	public function get_commit_id($branch_name = 'HEAD'): string {
		return $this->clear_string(`git -C "{$this->path}" rev-parse {$branch_name}`);
	}

	//  Get short id:				git show --format="%h" --no-patch
	public function get_commit_id_short($branch_name = 'HEAD'): string {
		return $this->clear_string($this->show('%h', $branch_name));
	}

	public function show($fmt, $ref = 'HEAD', $is_no_patch = true): string {
		$no_patch = $is_no_patch?'--no-patch':'';
		return `git -C "{$this->path}" show --format="{$fmt}" {$ref} {$no_patch}`;
	}

	//  Get author:					git show --format="%an" --no-patch
	public function get_author_name($ref = 'HEAD'): string {
		return $this->clear_string($this->show('%an', $ref));
	}

	//  Get email:					git show --format="%ae" --no-patch
	public function get_author_email($ref = 'HEAD'): string {
		return $this->clear_string($this->show('%ae', $ref));
	}

	//  Get timestamp:				git show --format="%at" --no-patch
	public function get_author_datetime($ref = 'HEAD'): ?DateTime {
		$ts = $this->show('%at', $ref);
		return !empty($ts)?DateTimeHelper::normalize((int) $ts):null;
	}

	//  Get committer:				git show --format="%cn" --no-patch
	public function get_committer_name($ref = 'HEAD'): string {
		return $this->clear_string($this->show('%cn', $ref));
	}

	//  Get committer email:		git show --format="%ce" --no-patch
	public function get_committer_email($ref = 'HEAD'): string {
		return $this->clear_string($this->show('%ce', $ref));
	}

	//  Get committer datetime:		git show --format="%cI" --no-patch
	public function get_committer_datetime($ref = 'HEAD'): ?DateTime {
		$ts = $this->show('%ct', $ref);
		return !empty($ts)?DateTimeHelper::normalize((int) $ts):null;
	}

	//  Subject:					git show --format="%s" --no-patch
	public function get_commit_subject($ref = 'HEAD'): string {
		return $this->clear_string($this->show('%s', $ref));
	}

	//  Body:						git show --format="%b" --no-patch
	public function get_commit_body($ref = 'HEAD'): string {
		return $this->show('%b', $ref);
	}

}