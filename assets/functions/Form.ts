import { Modal } from 'bootstrap'
import displayToast, { ToastType } from './ToastColor'

type Parameters = {
	requiredMessage?: string,
	minLengthMessage?: string,
	maxLengthMessage?: string,
	minTimeMessage?: string,
	maxTimeMessage?: string
};

class Form {

	private static instances = new Map()

	private readonly form: HTMLFormElement
	private button: HTMLButtonElement
	private buttonText: HTMLSpanElement
	private buttonSpinner: HTMLSpanElement

	private fields = new Map()
	private invalidFeedbacks = new Map()

	private validation: boolean = false
	private reset: boolean = true

	private dynamicDelay: NodeJS.Timeout = null

	private static deleteModal
	private static deleteForm

	private controller = null

	private static deleteForms = new Map()

	private static defaults: Parameters = {
		requiredMessage: 'Veuillez renseigner ce champ.',
		minLengthMessage: 'Please lengthen this text to contain at least {0} characters.',
		maxLengthMessage: 'Please lengthen this text to contain at most {0} characters.',
		minTimeMessage: 'L\'heure doit être égale ou postérieure à {0}.',
		maxTimeMessage: 'L\'heure doit être égale ou antérieure à {0}.',
	}

	static get(form: HTMLFormElement): Form {
		if (!form) {
			return
		}

		if (!Form.instances.has(form)) {
			Form.instances.set(form, new Form(form))
		}

		return Form.instances.get(form)
	}

	static setDefaults(parameters: Parameters) {
		Form.defaults = { ...Form.defaults, ...parameters }
	}

	constructor(form: HTMLFormElement) {
		this.form = form

		this.setFields()
	}

	public enableDynamic(delay: number = 500): Form {
		this.fields.forEach((field) => {
			const event = field instanceof HTMLInputElement ? 'input' : 'change'

			field.addEventListener(event, () => {
				if (this.dynamicDelay) {
					clearTimeout(this.dynamicDelay)
				}

				this.setLoading(true)

				this.dynamicDelay = setTimeout(() => {
					this.form.dispatchEvent(new Event('submit'))

					this.dynamicDelay = null
				}, delay)
			})
		})

		return this
	}

	public setLoading(isLoading: boolean): Form {
		if (!this.buttonText && !this.buttonSpinner) {
			const inner = this.button.innerHTML

			this.button.innerHTML = ''

			this.buttonText = document.createElement('span')
			this.buttonText.innerHTML = inner

			this.buttonSpinner = document.createElement('span')
			this.buttonSpinner.classList.add('spinner-border', 'spinner-border-sm', 'd-none')

			const buttonSpinnerText = document.createElement('span')
			buttonSpinnerText.classList.add('visually-hidden')
			buttonSpinnerText.innerText = 'Loading...'

			this.buttonSpinner.appendChild(buttonSpinnerText)
			this.button.appendChild(this.buttonText)
			this.button.appendChild(this.buttonSpinner)
		}

		if (isLoading) {
			this.button.disabled = true

			this.buttonText.classList.add('d-none')
			this.buttonSpinner.classList.remove('d-none')
		} else {
			this.button.disabled = false

			this.buttonSpinner.classList.add('d-none')
			this.buttonText.classList.remove('d-none')
		}

		return this
	}

	public hideErrors(): Form {
		this.invalidFeedbacks.forEach((invalidFeedback, field) => {
			field.classList.remove('is-invalid')
		})

		return this
	}

	public displayError(fieldName: string, errorMessage: string): Form {
		if (this.fields.has(fieldName)) {
			const field = this.fields.get(fieldName)

			if (!this.invalidFeedbacks.has(field)) {
				const nextElement = field.nextElementSibling

				console.log(errorMessage, field, nextElement)

				if (nextElement?.classList.contains('invalid-feedback')) {
					this.invalidFeedbacks.set(field, nextElement)
				} else {
					const div = document.createElement('div')
					div.classList.add('invalid-feedback')

					field.insertAdjacentElement('afterend', div)

					this.invalidFeedbacks.set(field, div)
				}
			}

			const invalidFeedback = this.invalidFeedbacks.get(field)
			invalidFeedback.innerHTML = errorMessage

			field.classList.add('is-invalid')
		}

		return this
	}

	public enableDelete(callback: Function = null): Form {
		if (Form.deleteForms.size === 0) {
			const modalElement = document.querySelector('.modal-delete')

			if (!modalElement) {
				console.error('Modal element does not exists.')

				return this
			}

			Form.deleteModal = new Modal(modalElement)

			const button = modalElement.querySelector('.btn-danger') as HTMLButtonElement

			button.addEventListener('click', () => {
				const form = Form.deleteForm
				const body = new FormData(form)
				button.disabled = true

				fetch(form.getAttribute('action') ?? window.location.href, {
					method: 'POST',
					body,
					headers: {
						Fetch: 'true',
					},
				}).then((response) => response.json()).then((data) => {
					if (data.success) {
						Form.deleteModal.hide()

						displayToast(ToastType.Success, data.message)

						const successCallback = Form.deleteForms.get(form)

						if (successCallback) {
							successCallback(data)
						}
					}
					button.disabled = false
				})
			})
		}

		this.button.addEventListener('click', event => {
			event.preventDefault()

			Form.deleteForm = this.form
			Form.deleteModal.show()
		})

		Form.deleteForms.set(this.form, callback)

		return this
	}

	public enableValidation(): Form {
		this.form.noValidate = true
		this.validation = true

		return this
	}

	public disableValidation(): Form {
		this.form.noValidate = false
		this.validation = false

		return this
	}

	public disableReset(): Form {
		this.reset = false

		return this
	}

	public enableFetch(successCallback: Function = null, errorCallback: Function = null): Form {
		this.form.addEventListener('submit', event => {
			event.preventDefault()

			this.fields.forEach((field) => field.classList.remove('is-invalid'))

			if (this.validation && this.checkFields()) {
				return
			}

			this.setLoading(true)

			const body = new FormData(this.form)

			// Trim spaces and more than one space in values
			body.forEach((value, key) => body.set(key, value.toString().replace(/\s\s+/g, ' ').trim()))

			let action = this.form.getAttribute('action') ?? window.location.origin + window.location.pathname
			const { method } = this.form

			if (this.controller) {
				this.controller.abort()
			}

			this.controller = new AbortController()
			const { signal } = this.controller

			let init: {
				method: string,
				headers: {
					Fetch: string
				},
				signal: AbortSignal,
				body?: FormData,
			} = {
				method,
				headers: {
					Fetch: 'true',
				},
				signal,
			};

			if (method === 'post') {
				init = { ...init, body }
			}

			if (method === 'get') {
				const searchParams = new URLSearchParams()

				body.forEach((value: string, key) => {
					if (value.length > 0) {
						searchParams.append(key, value.toString())
					}
				})

				if (searchParams.toString().length > 0) {
					action += `?${searchParams.toString()}`
				}

				window.history.replaceState({}, null, action)
			}

			fetch(action, init).then(response => response.json()).then(data => {
				if (data.errors) {
					Object.keys(data.errors).forEach((name) => {
						this.displayError(name, data.errors[name])
					})
				}

				if (data.success && this.reset) {
					this.form.reset()
				}

				if (data.message) {
					displayToast(data.success ? ToastType.Success : ToastType.Error, data.message)
				}

				this.setLoading(false)

				if (data.success && successCallback) {
					successCallback(data)
				}

				if (!data.success && errorCallback) {
					errorCallback(data)
				}

				this.controller = null
			}).catch((error) => {
				if (error.name !== 'AbortError') {
					this.setLoading(false)

					// throw new error
				}
			});
		});

		return this
	}

	private checkRequired(field: HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement): boolean {
		if (field.required && field.value.length === 0) {
			this.displayError(field.name, Form.defaults.requiredMessage)

			return true
		}

		return false
	}

	private checkMinTime(field: HTMLInputElement): boolean {
		if (field.min) {
			const min = field.min ? parseInt(field.min.toString().replace(':', '')) : null
			const value = field.value ? parseInt(field.value.toString().replace(':', '')) : null

			console.log('MinTime', field.name, value, min)

			if (value < min) {
				this.displayError(field.name, Form.defaults.minTimeMessage.replace('{0}', field.min.toString()))

				return true
			}
		}

		return false
	}

	private checkMaxTime(field: HTMLInputElement): boolean {
		if (field.max) {
			const max = field.max ? parseInt(field.max.toString().replace(':', '')) : null
			const value = field.value ? parseInt(field.value.toString().replace(':', '')) : null

			console.log('MaxTime', field.name, value, max)

			if (value > max) {
				this.displayError(field.name, Form.defaults.maxTimeMessage.replace('{0}', field.max.toString()))

				return true
			}
		}

		return false
	}

	private checkMinLength(field: HTMLInputElement | HTMLTextAreaElement): boolean {
		const minLength = field.minLength
		const length = field.value.length

		if (minLength > -1 && length > 0) {

			console.log('MinLength', field.name, length, minLength)

			if (minLength > length) {
				this.displayError(field.name, Form.defaults.minLengthMessage.replace('{0}', minLength.toString()))

				return true
			}
		}

		return false
	}

	private checkMaxLength(field: HTMLInputElement | HTMLTextAreaElement): boolean {
		const maxLength = field.maxLength
		const length = field.value.length

		if (maxLength > -1 && length > 0) {

			console.log('MaxLength', field.name, length, maxLength)

			if (maxLength < length) {
				this.displayError(field.name, Form.defaults.maxLengthMessage.replace('{0}', maxLength.toString()))

				return true
			}
		}

		return false
	}

	private checkFields(): boolean {
		const fields = [].slice.call(this.form.querySelectorAll('input, select, textarea')) as HTMLInputElement[] | HTMLSelectElement[] | HTMLTextAreaElement[]
		const checks = ['checkRequired', 'checkMinTime', 'checkMaxTime', 'checkMinLength', 'checkMaxLength']

		let formHasError = false

		fields.forEach((field) => {
			if (!this.fields.has(field.name)) {
				this.fields.set(field.name, field)
			}

			let fieldHasError = false

			checks.forEach((check: string) => {
				if (fieldHasError === true) {
					return
				}

				fieldHasError = this[check](field)
			});

			if (fieldHasError) {
				formHasError = true
			}
		});

		return formHasError;
	}

	private setFields() {
		this.form.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach((field: HTMLInputElement) => {
			const fieldName = field.name
			const subName = fieldName.substring(fieldName.indexOf('[') + 1, fieldName.lastIndexOf(']'))

			this.fields.set(subName !== '' ? subName : fieldName, field)
		});

		this.button = this.form.querySelector('button[type="submit"]')
	}

}

export default Form
