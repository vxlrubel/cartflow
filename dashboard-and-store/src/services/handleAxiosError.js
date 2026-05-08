// src/services/handleAxiosError.js

/**
 * Handle Axios Request Errors
 *
 * Usage:
 *
 * catch (err) {
 *   const response = handleAxiosError(err)
 *
 *   toastStatus.value = response.status
 *   toastMessage.value = response.message
 *
 *   if (response.errors) {
 *     errors.value = response.errors
 *   }
 * }
 */

const handleAxiosError = (error) => {

  // Default Response
  const response = {
    status: 'error',
    message: 'Something went wrong!',
    errors: {}
  }

  /**
   * ----------------------------------------
   * Network Error
   * ----------------------------------------
   */
  if (error.code === 'ERR_NETWORK') {
    response.message =
      'Network error! Please check your internet connection.'

    return response
  }

  /**
   * ----------------------------------------
   * Timeout Error
   * ----------------------------------------
   */
  if (error.code === 'ECONNABORTED') {
    response.message =
      'Request timeout! Please try again.'

    return response
  }

  /**
   * ----------------------------------------
   * No Server Response
   * ----------------------------------------
   */
  if (!error.response) {
    response.message =
      'No response from server.'

    return response
  }

  const status = error.response.status
  const data = error.response.data

  /**
   * ----------------------------------------
   * Validation Error - 422
   * ----------------------------------------
   */
  if (status === 422) {
    response.message =
      data?.message || 'Please fix the validation errors.'

    response.errors = data?.errors || {}

    return response
  }

  /**
   * ----------------------------------------
   * Unauthorized - 401
   * ----------------------------------------
   */
  if (status === 401) {
    response.message =
      data?.message || 'Unauthorized access.'

    return response
  }

  /**
   * ----------------------------------------
   * Forbidden - 403
   * ----------------------------------------
   */
  if (status === 403) {
    response.message =
      data?.message || 'You do not have permission.'

    return response
  }

  /**
   * ----------------------------------------
   * Not Found - 404
   * ----------------------------------------
   */
  if (status === 404) {
    response.message =
      data?.message || 'Requested resource not found.'

    return response
  }

  /**
   * ----------------------------------------
   * Too Many Requests - 429
   * ----------------------------------------
   */
  if (status === 429) {
    response.message =
      data?.message || 'Too many requests. Please try again later.'

    return response
  }

  /**
   * ----------------------------------------
   * Server Error - 500+
   * ----------------------------------------
   */
  if (status >= 500) {
    response.message =
      data?.message || 'Server error! Please try again later.'

    return response
  }

  /**
   * ----------------------------------------
   * Fallback Error Message
   * ----------------------------------------
   */
  response.message =
    data?.message || response.message

  return response
}

export default handleAxiosError