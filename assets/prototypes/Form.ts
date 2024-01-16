export {}

declare global {
    interface HTMLFormElement {
        serialize(): FormData
    }
}

HTMLFormElement.prototype.serialize = function () {
    const body = new FormData()

    const fields = Array.from(this.querySelectorAll('input[name], textarea[name], select[name]')) as (HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement)[]

    const indexes = new Map()

    fields.forEach(field => {
        if (field instanceof HTMLSelectElement && field.multiple) {
            Array.from(field.selectedOptions).forEach((selectedOption, index) => {
                const name = field.name.slice(0, -1) + index + ']'

                body.set(name, selectedOption.value)
            })
        } else {
            const name = field.name

            if (field instanceof HTMLInputElement && field.type === 'file') {
                const file = field.files[0]

                body.append(name, file)
            } else if (field instanceof HTMLInputElement && field.type === 'checkbox') {
                if (field.checked) {
                    if (field.name.endsWith('[]')) {
                        if (!indexes.has(field.name)) {
                            indexes.set(field.name, 0)
                        }

                        const index = indexes.get(field.name)

                        const name = field.name.slice(0, -1) + index + ']'

                        indexes.set(field.name, index + 1)

                        body.set(name, field.value)
                    } else {
                        body.set(name, field.value)
                    }
                }
            } else {
                const value = field.value.toString().replace(/\s\s+/g, ' ').trim()

                if (value.length) {
                    body.set(name, value)
                }
            }
        }
    })

    return body
}
