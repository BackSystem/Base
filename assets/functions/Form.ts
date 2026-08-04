import displayToast, { ToastType } from './ToastColor'

type FormParameters = {
    enableButtonAfterSuccess?: boolean
}

type FormField =
    | HTMLInputElement
    | HTMLTextAreaElement
    | HTMLSelectElement

type FormErrors = Record<string, string | string[]>

export type FormResponse = {
    success: boolean
    message?: string
    errors?: FormErrors
    redirect?: string
}

type FormCallback = (data: FormResponse) => void

class Form {
    private static instances = new Map<HTMLFormElement, Form>()

    private static defaults: FormParameters = {
        enableButtonAfterSuccess: true,
    }

    private readonly form: HTMLFormElement

    private readonly submitButtonTexts =
        new Map<HTMLButtonElement, HTMLSpanElement>()

    private readonly submitButtonSpinners =
        new Map<HTMLButtonElement, HTMLElement>()

    private reset = true

    private dynamicDelay: ReturnType<typeof setTimeout> | null = null

    private controller: AbortController | null = null

    private parameters: FormParameters = {}

    public static get(form: HTMLFormElement): Form {
        let instance = Form.instances.get(form)

        if (!instance) {
            instance = new Form(form)
            Form.instances.set(form, instance)
        }

        return instance
    }

    public static setDefaults(parameters: FormParameters): void {
        Form.defaults = {
            ...Form.defaults,
            ...parameters,
        }
    }

    public constructor(form: HTMLFormElement) {
        this.form = form
        this.parameters = { ...Form.defaults }

        this.getFields().forEach(field => {
            this.detectChange(field)
        })

        new MutationObserver(mutations => {
            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    if (!(node instanceof HTMLElement)) {
                        return
                    }

                    if (
                        node instanceof HTMLInputElement ||
                        node instanceof HTMLTextAreaElement ||
                        node instanceof HTMLSelectElement
                    ) {
                        this.detectChange(node)
                    }

                    node
                        .querySelectorAll<FormField>(
                            'input[name]:not([type="hidden"]), ' +
                            'textarea[name], ' +
                            'select[name]'
                        )
                        .forEach(field => {
                            this.detectChange(field)
                        })
                })
            })
        }).observe(this.form, {
            attributes: false,
            childList: true,
            characterData: false,
            subtree: true,
        })
    }

    private detectChange(field: FormField): Form {
        if (
            field instanceof HTMLInputElement ||
            field instanceof HTMLTextAreaElement
        ) {
            field.addEventListener('input', () => {
                const selector =
                    `input[name="${CSS.escape(field.name)}"], ` +
                    `textarea[name="${CSS.escape(field.name)}"]`

                this.form
                    .querySelectorAll<HTMLInputElement | HTMLTextAreaElement>(
                        selector
                    )
                    .forEach(element => {
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

    public setConfiguration(parameters: FormParameters): Form {
        this.parameters = {
            ...Form.defaults,
            ...parameters,
        }

        return this
    }

    public enableDynamic(delay: number = 500): Form {
        this.getFields().forEach(field => {
            const eventType =
                field instanceof HTMLInputElement ? 'input' : 'change'

            field.addEventListener(eventType, () => {
                if (this.dynamicDelay !== null) {
                    clearTimeout(this.dynamicDelay)
                }

                this.setLoading(true)

                this.dynamicDelay = setTimeout(() => {
                    this.form.dispatchEvent(
                        new Event('submit', {
                            bubbles: true,
                            cancelable: true,
                        })
                    )

                    this.dynamicDelay = null
                }, delay)
            })
        })

        return this
    }

    public setLoading(isLoading: boolean): Form {
        const submitButtons =
            this.form.querySelectorAll<HTMLButtonElement>(
                'button[type="submit"]'
            )

        submitButtons.forEach(submitButton => {
            if (!this.submitButtonTexts.has(submitButton)) {
                const inner = submitButton.innerHTML

                submitButton.innerHTML = ''

                const buttonText = document.createElement('span')
                buttonText.innerHTML = inner

                const buttonSpinner = document.createElement('i')
                buttonSpinner.classList.add(
                    'fa-duotone',
                    'fa-fw',
                    'fa-spinner-third',
                    'fa-spin',
                    'd-none'
                )

                submitButton.appendChild(buttonText)
                submitButton.appendChild(buttonSpinner)

                this.submitButtonTexts.set(submitButton, buttonText)
                this.submitButtonSpinners.set(
                    submitButton,
                    buttonSpinner
                )
            }

            const buttonText =
                this.submitButtonTexts.get(submitButton)

            const buttonSpinner =
                this.submitButtonSpinners.get(submitButton)

            if (!buttonText || !buttonSpinner) {
                return
            }

            submitButton.disabled = isLoading

            if (isLoading) {
                buttonText.classList.add('d-none')
                buttonSpinner.classList.remove('d-none')
            } else {
                buttonSpinner.classList.add('d-none')
                buttonText.classList.remove('d-none')
            }
        })

        return this
    }

    public hideErrors(fieldName?: string): Form {
        let invalidFeedbackContainers: NodeListOf<HTMLDivElement>

        if (fieldName) {
            invalidFeedbackContainers =
                this.form.querySelectorAll<HTMLDivElement>(
                    `div.invalid-feedbacks[data-field="${CSS.escape(fieldName)}"]`
                )
        } else {
            invalidFeedbackContainers =
                this.form.querySelectorAll<HTMLDivElement>(
                    'div.invalid-feedbacks'
                )
        }

        invalidFeedbackContainers.forEach(container => {
            container.innerHTML = ''
        })

        return this
    }

    public displayError(
        fieldName: string,
        errorMessages: string | string[]
    ): Form {
        const fields = this.form.querySelectorAll<FormField>(
            `[name="${CSS.escape(fieldName)}"]`
        )

        fields.forEach(field => {
            if (
                field instanceof HTMLInputElement &&
                (field.type === 'checkbox' || field.type === 'radio')
            ) {
                field.classList.add('is-invalid')
                return
            }

            let element: Element = field

            if (
                field instanceof HTMLInputElement &&
                field.classList.contains('btn-check')
            ) {
                const label = this.form.querySelector<HTMLLabelElement>(
                    `label[for="${CSS.escape(field.id)}"]`
                )

                if (field.nextElementSibling === label && label) {
                    element = label
                }
            }

            const invalidFeedbackContainers =
                this.form.querySelectorAll<HTMLDivElement>(
                    `div.invalid-feedbacks[data-field="${CSS.escape(field.name)}"]`
                )

            if (invalidFeedbackContainers.length > 0) {
                invalidFeedbackContainers.forEach(container => {
                    this.appendErrorMessages(
                        container,
                        errorMessages
                    )
                })
            } else {
                const container = document.createElement('div')

                container.classList.add('invalid-feedbacks')
                container.dataset.field = field.name

                this.appendErrorMessages(container, errorMessages)

                element.insertAdjacentElement('afterend', container)
            }

            field.classList.add('is-invalid')
        })

        return this
    }

    private appendErrorMessages(
        container: HTMLDivElement,
        errorMessages: string | string[]
    ): void {
        const messages = Array.isArray(errorMessages)
            ? errorMessages
            : [errorMessages]

        messages.forEach(errorMessage => {
            const invalidFeedback = document.createElement('div')

            invalidFeedback.classList.add(
                'invalid-feedback',
                'd-block'
            )

            invalidFeedback.innerHTML = errorMessage

            container.appendChild(invalidFeedback)
        })
    }

    public disableReset(): Form {
        this.reset = false

        return this
    }

    public enableFetch(
        successCallback: FormCallback | null = null,
        errorCallback: FormCallback | null = null,
        preserveQueryParams: boolean = false
    ): Form {
        this.form.addEventListener('submit', event => {
            event.preventDefault()

            this.getFields().forEach(field => {
                field.classList.remove('is-invalid')
            })

            this.setLoading(true)
            this.hideErrors()

            const body = this.form.serialize()

            const action =
                this.form.getAttribute('action') ??
                window.location.origin + window.location.pathname

            const method = this.form.method.toLowerCase()

            this.controller?.abort()
            this.controller = new AbortController()

            const actionUrl = new URL(action, window.location.href)

            if (preserveQueryParams) {
                const activeUrl = new URL(window.location.href)

                activeUrl.searchParams.forEach((value, name) => {
                    actionUrl.searchParams.append(name, value)
                })
            }

            const init: RequestInit = {
                method,
                headers: {
                    Fetch: 'true',
                },
                signal: this.controller.signal,
            }

            if (method === 'post') {
                init.body = body
            }

            if (method === 'get') {
                body.forEach((value: FormDataEntryValue, key: string) => {
                    const stringValue = value instanceof File ? value.name : value

                    if (stringValue.length > 0) {
                        actionUrl.searchParams.append(key, stringValue)
                    }
                })

                window.history.replaceState(
                    {},
                    '',
                    actionUrl.toString()
                )
            }

            fetch(actionUrl, init)
                .then(async response => {
                    if (
                        response.redirected &&
                        response.url.includes('login')
                    ) {
                        window.location.reload()
                        return null
                    }

                    return response.json() as Promise<FormResponse>
                })
                .then(data => {
                    if (!data) {
                        return
                    }

                    if (data.errors) {
                        Object.entries(data.errors).forEach(
                            ([name, messages]) => {
                                this.displayError(name, messages)
                            }
                        )
                    }

                    if (data.success && this.reset) {
                        this.form.reset()
                    }

                    if (data.message) {
                        displayToast(
                            data.success
                                ? ToastType.Success
                                : ToastType.Error,
                            data.message
                        )
                    }

                    if (
                        !data.success ||
                        this.parameters.enableButtonAfterSuccess
                    ) {
                        this.setLoading(false)
                    }

                    if (data.success) {
                        successCallback?.(data)
                    } else {
                        errorCallback?.(data)
                    }

                    this.controller = null
                })
                .catch((error: unknown) => {
                    if (
                        error instanceof DOMException &&
                        error.name === 'AbortError'
                    ) {
                        return
                    }

                    this.setLoading(false)

                    if (error instanceof Error) {
                        console.error(error.message)
                    } else {
                        console.error(error)
                    }
                })
        })

        return this
    }

    private getFields(): FormField[] {
        return Array.from(
            this.form.querySelectorAll<FormField>(
                'input[name]:not([type="hidden"]), ' +
                'textarea[name], ' +
                'select[name]'
            )
        )
    }
}

export default Form
