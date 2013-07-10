<?php

namespace Krautoload;

/**
 * To help testability, we use an injected API instead of just a return value.
 * The injected API can be mocked to provide a mocked file_exists(), and to
 * monitor all suggested candidates, not just the correct return value.
 */
interface InjectedAPI_ClassFinder_Interface {

  /**
   * Get the name of the class we are looking for.
   *
   * @return string
   *   The class we are looking for.
   */
  function getClass();

  /**
   * Suggest a file that, if the file exists,
   * HAS TO declare the class we are looking for.
   *
   * @param string $file
   *   The file that is supposed to declare the class.
   *
   * @return boolean|NULL
   *   TRUE, if we are not interested in further candidates.
   *   FALSE|NULL, if we are interested in further candidates.
   */
  function guessFile($file);

  /**
   * Suggest a file that, if the file exists,
   * MAY declare the class we are looking for.
   *
   * @param string $file
   *   The file that is supposed to declare the class.
   *
   * @return boolean|NULL
   *   TRUE, if we are not interested in further candidates.
   *   FALSE|NULL, if we are interested in further candidates.
   */
  function guessFileCandidate($file);

  /**
   * Suggest a file that HAS TO declare the class we are looking for.
   *
   * Unlike guessFile(), claimFile() being called means that the caller is sure
   * that the file does exist. Thus, we can skip the is_file() check, saving a
   * few nanoseconds.
   *
   * This is useful if a plugin already did the is_file() check by itself.
   *
   * @param string $file
   *   The file that is supposed to declare the class.
   *
   * @return boolean|NULL
   *   TRUE, if we are not interested in further candidates.
   *   FALSE|NULL, if we are interested in further candidates.
   */
  function claimFile($file);

  /**
   * Suggest a file that MAY declare the class we are looking for.
   *
   * Unlike guessFile(), claimFile() being called means that the caller is sure
   * that the file does exist. Thus, we can skip the is_file() check, saving a
   * few nanoseconds.
   *
   * This is useful if a plugin already did the is_file() check by itself.
   *
   * @param string $file
   *   The file that is supposed to declare the class.
   *
   * @return boolean|NULL
   *   TRUE, if we are not interested in further candidates.
   *   FALSE|NULL, if we are interested in further candidates.
   */
  function claimFileCandidate($file);

  /**
   * Suggest a file that, if the file exists,
   * HAS TO declare the class we are looking for.
   *
   * Unlike guessFile(), this one checks the full PHP include path.
   *
   * @param string $file
   *   The file that is supposed to declare the class.
   *
   * @return boolean|NULL
   *   TRUE, if we are not interested in further candidates.
   *   FALSE|NULL, if we are interested in further candidates.
   */
  function guessFile_checkIncludePath($file);

  /**
   * Suggest a file that, if the file exists,
   * MAY declare the class we are looking for.
   *
   * Unlike guessFile(), this one checks the full PHP include path.
   *
   * @param string $file
   *   The file that is supposed to declare the class.
   *
   * @return boolean|NULL
   *   TRUE, if we are not interested in further candidates.
   *   FALSE|NULL, if we are interested in further candidates.
   */
  function guessFileCandidate_checkIncludePath($file);
}
