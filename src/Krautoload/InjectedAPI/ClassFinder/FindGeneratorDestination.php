<?php

namespace Krautoload;

class InjectedAPI_ClassFinder_FindGeneratorDestination extends InjectedAPI_ClassFinder_Abstract {

  protected $destination;

  function getDestination() {
    return $this->destination;
  }

  /**
   * Suggest a file that, if the file exists,
   * HAS TO declare the class we are looking for.
   * Include that file, if it exists.
   *
   * @param string $file
   *   The file that is supposed to declare the class.
   *
   * @return boolean
   *   TRUE, if the file exists.
   *   FALSE, otherwise.
   */
  function guessFile($file) {
    $this->destination = $file;
    return TRUE;
  }

  /**
   * Suggest a file that, if the file exists,
   * MAY declare the class we are looking for.
   * Include that file, if it exists.
   *
   * @param string $file
   *   The file that is supposed to declare the class.
   *
   * @return boolean
   *   TRUE, if the file exists and the class exists after file inclusion.
   *   FALSE, otherwise.
   */
  function guessFileCandidate($file) {
    $this->destination = $file;
    return TRUE;
  }

  /**
   * Suggest a file that HAS TO declare the class we are looking for.
   * Include that file.
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
   * @return boolean
   *   Always TRUE, because we assume the file does exist and does define the
   *   class.
   */
  function claimFile($file) {
    $this->destination = $file;
    return TRUE;
  }

  /**
   * Suggest a file that MAY declare the class we are looking for.
   * Include that file.
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
   * @return boolean
   *   TRUE, if the class exists after file inclusion.
   *   FALSE, otherwise
   */
  function claimFileCandidate($file) {
    $this->destination = $file;
    return TRUE;
  }

  /**
   * Suggest a file that, if the file exists,
   * HAS TO declare the class we are looking for.
   * Include that file, if it exists.
   *
   * Unlike guessFile(), this one checks the full PHP include path.
   *
   * @param string $file
   *   The file that is supposed to declare the class.
   *
   * @return boolean
   *   TRUE, if the file exists.
   *   FALSE, otherwise.
   */
  function guessFile_checkIncludePath($file) {
    $this->destination = $file;
    return TRUE;
  }

  /**
   * Suggest a file that, if the file exists,
   * MAY declare the class we are looking for.
   * Include that file, if it exists.
   *
   * Unlike guessFile(), this one checks the full PHP include path.
   *
   * @param string $file
   *   The file that is supposed to declare the class.
   *
   * @return boolean
   *   TRUE, if the file exists and the class exists after file inclusion.
   *   FALSE, otherwise.
   */
  function guessFileCandidate_checkIncludePath($file) {
    $this->destination = $file;
    return TRUE;
  }
}
