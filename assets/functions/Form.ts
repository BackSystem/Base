import displayToast, { ToastType } from '@Base/functions/ToastColor'

type Parameters = {
    enableButtonAfterSuccess?: boolean,
}

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
        Form.defaults = {...Form.defaults, ...parameters}
    }

    constructor(form: HTMLFormElement) {
        this.form = form

        this.parameters = Form.defaults

        this.button = this.form.querySelector('button[type="submit"]')
    }

    public setConfiguration(parameters: Parameters): Form {
        this.parameters = {...Form.defaults, ...parameters}

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
        if (!this.buttonText && !this.buttonSpinner) {
            const inner = this.button.innerHTML

            this.button.innerHTML = ''

            this.buttonText = document.createElement('span')
            this.buttonText.innerHTML = inner

            this.buttonSpinner = document.createElement('i')
            this.buttonSpinner.classList.add('fa-duotone', 'fa-fw', 'fa-spinner-third', 'fa-spin', 'd-none')

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
        const field = this.form.querySelector('[name="' + fieldName + '"]')

        if (field) {
            const nextElement = field.nextElementSibling
            let invalidFeedback

            if (nextElement?.classList.contains('invalid-feedback')) {
                invalidFeedback = nextElement
            } else {
                invalidFeedback = document.createElement('div')
                invalidFeedback.classList.add('invalid-feedback')

                field.insertAdjacentElement('afterend', invalidFeedback)
            }

            invalidFeedback.innerHTML = errorMessage

            field.classList.add('is-invalid')
        }

        return this
    }

    public disableReset(): Form {
        this.reset = false

        return this
    }

    public enableFetch(successCallback: Function = null, errorCallback: Function = null): Form {
        this.form.addEventListener('submit', event => {
            event.preventDefault()

            const fields = this.getFields()

            fields.forEach((field: HTMLInputElement) => {
                field.classList.remove('is-invalid')
            })

            this.setLoading(true)

            const body = this.form.serialize()

            let action = this.form.getAttribute('action') ?? window.location.origin + window.location.pathname
            const {method} = this.form

            if (this.controller) {
                this.controller.abort()
            }

            this.controller = new AbortController()
            const {signal} = this.controller

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
                init = {...init, body}
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

            fetch(action, init).then(response => {
                if (response.redirected) {
                    window.location.href = response.url
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

                if (this.parameters.enableButtonAfterSuccess) {
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

                    throw new error
                }
            })
        })

        return this
    }

    private getFields(): (HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement)[] {
        return Array.from(this.form.querySelectorAll('input[name], textarea[name], select[name]'))
    }

}

export default Form
