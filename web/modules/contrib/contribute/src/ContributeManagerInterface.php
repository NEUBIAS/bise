<?php

namespace Drupal\contribute;

/**
 * Interface ContributeManagerInterface.
 */
interface ContributeManagerInterface {

  /**
   * Get account status.
   *
   * @return array
   *   An associative array containing account status.
   */
  public function getAccount();

  /**
   * Get membership status.
   *
   * @return array
   *   An associative array containing membership status.
   */
  public function getMembership();

  /**
   * Get contribution status.
   *
   * @return array
   *   An associative array containing contribution status.
   */
  public function getContribution();

  /**
   * Get contribute status.
   *
   * @return bool
   *   The contribute status.
   */
  public function getStatus();

  /**
   * Get account type.
   *
   * @return string|null
   *   Get the account type.
   */
  public function getAccountType();

  /**
   * Get account id.
   *
   * @return string|null
   *   Get the account id.
   */
  public function getAccountId();

  /**
   * Set account type.
   *
   * @param string|null $account_type
   *   The account type.
   */
  public function setAccountType($account_type);

  /**
   * Set account id.
   *
   * @param string|null $account_id
   *   The account id.
   */
  public function setAccountId($account_id);

}
