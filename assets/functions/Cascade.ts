type Fields = { [key: string]: string | string[] }

export default class Cascade {

    private static instance: Cascade

    private static list: Fields[] = []

    public static set(fields: Fields) {
        if (!Cascade.instance) {
            Cascade.instance = new Cascade()
        }

        Cascade.list.push(fields)
    }

    constructor() {
        document.addEventListener('change', event => {
            const element = event.target as HTMLElement

            if (element instanceof HTMLSelectElement) {
                const name = element.getAttribute('name')

                Cascade.list.forEach(fields => {

                    if (fields.hasOwnProperty(name)) {
                        const form = element.closest('form')
                        const children = this.getChildrenToDisable(fields, name)

                        children.forEach(childName => {
                            const child = form.querySelector('select[name="' + childName + '"]') as HTMLSelectElement

                            if (child) {
                                child.disabled = true
                            }
                        })

                        const method = form.method ?? 'post'
                        const body = new FormData(form)

                        let action = form.getAttribute('action') ?? window.location.origin + window.location.pathname
                        let init = { method }

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
                        } else {
                            init['body'] = body
                        }

                        fetch(action, init).then(response => response.text()).then(data => {
                            const body = new DOMParser().parseFromString(data, 'text/html').body

                            children.forEach(childName => {
                                this.replaceElement(body, childName)
                            })
                        })
                    }
                })
            }
        })
    }

    private getChildrenToDisable(fields: Fields, name: string) {
        let array = []

        if (fields.hasOwnProperty(name)) {
            const target = fields[name]

            if (Array.isArray(target)) {
                target.forEach(child => {
                    array.push(child)

                    array = [...array, ...this.getChildrenToDisable(fields, child)]
                })
            } else {
                array.push(target)

                array = [...array, ...this.getChildrenToDisable(fields, target)]
            }
        }

        return array
    }

    private replaceElement(body: HTMLElement, name: string) {
        const select = document.querySelector('select[name="' + name + '"]')

        if (select) {
            const newSelect = body.querySelector('select[name="' + name + '"]')

            if (newSelect) {
                select.closest('div').replaceWith(newSelect.closest('div'))

                newSelect.classList.remove('is-invalid')

                const invalidFeedback = newSelect.closest('div').querySelector('.invalid-feedback')

                if (invalidFeedback) {
                    invalidFeedback.classList.remove('d-block')
                }
            }
        }
    }

}