import displayToast, { ToastType } from './ToastColor'

type Parameters = {
    enableButtonAfterSuccess?: boolean,
}

class Form {

    private static instances = new Map()

    private readonly form: HTMLFormElement

    private fields = new Map()

    private submitButtonTexts = new Map()
    private submitButtonSpinners = new Map()

    private reset: boolean = true

    private dynamicDelay: NodeJS.Timeout = null

    private controller = null

    private static defaults: Parameters = {
        enableButtonAfterSuccess: true,
    }

    private parameters: Parameters = {}

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

        this.parameters = Form.defaults

        this.button = this.form.querySelector<HTMLButtonElement>('button[type="submit"]')

        this.getFields().forEach(field => {
            this.detectChange(field)
        })

        new MutationObserver(mutations => {
            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    if (node instanceof HTMLElement) {
                        if (node instanceof HTMLInputElement || node instanceof HTMLTextAreaElement || node instanceof HTMLSelectElement) {
                            this.detectChange(node)
                        }

                        node.querySelectorAll<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>('input[name]:not([type="hidden"]), textarea[name], select[name]').forEach(field => {
                            this.detectChange(field)
                        })
                    }
                })
            })
        }).observe(this.form, {
            attributes: false,
            childList: true,
            characterData: false,
            subtree: true,
        })
    }

    private detectChange(field: HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement): Form {
        if (field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement) {
            field.addEventListener('input', () => {
                this.form.querySelectorAll<HTMLInputElement | HTMLTextAreaElement>('input[name="' + field.name + '"], textarea[name="' + field.name + '"]').forEach(element => {
                    if (element.checkValidity()) {
                        element.classList.remove('is-invalid')

                        this.hideErrors(field.name)
                    }
                })
            })
        }

        if (field instanceof HTMLSelectElement) {
            field.addEventListener('change', () => {
                if (field.checkValidity()) {
                    field.classList.remove('is-invalid')

                    this.hideErrors(field.name)
                }
            })
        }

        return this
    }

    public setConfiguration(parameters: Parameters): Form {
        this.parameters = { ...Form.defaults, ...parameters }

        return this
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
        const submitButtons = this.form.querySelectorAll<HTMLButtonElement>('button[type="submit"]')

        submitButtons.forEach(submitButton => {
            if (!this.submitButtonTexts.has(submitButton)) {
                const inner = submitButton.innerHTML

                submitButton.innerHTML = ''

                const buttonText = document.createElement('span')
                buttonText.innerHTML = inner

                const buttonSpinner = document.createElement('i')
                buttonSpinner.classList.add('fa-duotone', 'fa-fw', 'fa-spinner-third', 'fa-spin', 'd-none')

                submitButton.appendChild(buttonText)
                submitButton.appendChild(buttonSpinner)

                this.submitButtonTexts.set(submitButton, buttonText)
                this.submitButtonSpinners.set(submitButton, buttonSpinner)
            }

            if (isLoading) {
                submitButton.disabled = true

                this.submitButtonTexts.get(submitButton).classList.add('d-none')
                this.submitButtonSpinners.get(submitButton).classList.remove('d-none')
            } else {
                submitButton.disabled = false

                this.submitButtonSpinners.get(submitButton).classList.add('d-none')
                this.submitButtonTexts.get(submitButton).classList.remove('d-none')
            }
        })

        return this
    }

    public hideErrors(fieldName?: string): Form {
        let invalidFeedbacksContainersDiv: NodeListOf<HTMLDivElement>

        if (fieldName) {
            invalidFeedbacksContainersDiv = this.form.querySelectorAll<HTMLDivElement>(`div.invalid-feedbacks[data-field="${fieldName}"]`)
        } else {
            invalidFeedbacksContainersDiv = this.form.querySelectorAll<HTMLDivElement>('div.invalid-feedbacks')
        }

        invalidFeedbacksContainersDiv.forEach(invalidFeedbacksContainerDiv => {
            invalidFeedbacksContainerDiv.innerHTML = ''
        })

        return this
    }

    public displayError(fieldName: string, errorMessages: string | string[]): Form {
        const fields = this.form.querySelectorAll<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>('[name="' + fieldName + '"]')

        fields.forEach(field => {
            if (field.type !== 'checkbox' && field.type !== 'radio') {
                let element = field

                if (field.classList.contains('btn-check')) {
                    if (field.nextElementSibling === document.querySelector('label[for="' + field.id + '"]')) {
                        if (field.nextElementSibling instanceof HTMLInputElement) {
                            element = field.nextElementSibling
                        }
                    }
                }

                let invalidFeedbacksContainersDiv = this.form.querySelectorAll<HTMLDivElement>('div.invalid-feedbacks[data-field="' + field.name + '"]')

                if (invalidFeedbacksContainersDiv.length) {
                    invalidFeedbacksContainersDiv.forEach(invalidFeedbacksContainerDiv => {
                        if (Array.isArray(errorMessages)) {
                            errorMessages.forEach(errorMessage => {
                                const invalidFeedbackDiv = document.createElement('div')
                                invalidFeedbackDiv.classList.add('invalid-feedback', 'd-block')
                                invalidFeedbackDiv.innerHTML = errorMessage

                                invalidFeedbacksContainerDiv.appendChild(invalidFeedbackDiv)
                            })
                        } else {
                            const invalidFeedbackDiv = document.createElement('div')
                            invalidFeedbackDiv.classList.add('invalid-feedback', 'd-block')
                            invalidFeedbackDiv.innerHTML = errorMessages

                            invalidFeedbacksContainerDiv.appendChild(invalidFeedbackDiv)
                        }
                    })
                } else {
                    const invalidFeedbacksContainerDiv = document.createElement('div')
                    invalidFeedbacksContainerDiv.classList.add('invalid-feedbacks')
                    invalidFeedbacksContainerDiv.dataset.field = field.name

                    if (Array.isArray(errorMessages)) {
                        errorMessages.forEach(errorMessage => {
                            const invalidFeedbackDiv = document.createElement('div')
                            invalidFeedbackDiv.classList.add('invalid-feedback', 'd-block')
                            invalidFeedbackDiv.innerHTML = errorMessage

                            invalidFeedbacksContainerDiv.appendChild(invalidFeedbackDiv)
                        })
                    } else {
                        const invalidFeedbackDiv = document.createElement('div')
                        invalidFeedbackDiv.classList.add('invalid-feedback', 'd-block')
                        invalidFeedbackDiv.innerHTML = errorMessages

                        invalidFeedbacksContainerDiv.appendChild(invalidFeedbackDiv)
                    }

                    element.insertAdjacentElement('afterend', invalidFeedbacksContainerDiv)
                }
            }

            field.classList.add('is-invalid')
        })

        return this
    }

    public disableReset(): Form {
        this.reset = false

        return this
    }

    public enableFetch(successCallback: Function = null, errorCallback: Function = null, preserveQueryParams: boolean = false): Form {
        this.form.addEventListener('submit', event => {
            event.preventDefault()

            const fields = this.getFields()

            fields.forEach((field: HTMLInputElement) => {
                field.classList.remove('is-invalid')
            })

            this.setLoading(true)
            this.hideErrors()

            const body = this.form.serialize()

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

            const actionUrl = new URL(action)

            if (preserveQueryParams) {
                const activeUrl = new URL(window.location.href)

                activeUrl.searchParams.forEach((value: string, name: string) => {
                    actionUrl.searchParams.append(name, value)
                })
            }

            if (method === 'get') {
                body.forEach((value: string, key: string) => {
                    if (value.length > 0) {
                        actionUrl.searchParams.append(key, value.toString())
                    }
                })

                window.history.replaceState({}, null, actionUrl)
            }

            fetch(actionUrl, init).then(response => {
                if (response.redirected && response.url.includes('login')) {
                    window.location.reload()

                    return
                }

                return response.json()
            }).then(data => {
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

                if (!data.success || data.success && this.parameters.enableButtonAfterSuccess) {
                    this.setLoading(false)
                }

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

                    console.error(error.message)
                }
            })
        })

        return this
    }

    private getFields(): (HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement)[] {
        return Array.from(this.form.querySelectorAll('input[name]:not([type="hidden"]), textarea[name], select[name]'))
    }

}

export default Form
