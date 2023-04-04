import slideDown from './slideDown'
import slideUp from './slideUp'

export enum ToastType {
	Success = 'success',
	Error = 'danger',
	Warning = 'warning',
	Info = 'info'
}

let toastContainer = document.querySelector('.toast-container')

export default function displayToast (type: ToastType, text: string, duration: number = 5000) {
	const toastBody = document.createElement('div')
	toastBody.classList.add('toast-body')
	toastBody.innerText = text

	const toast = document.createElement('div')
	toast.classList.add('toast', 'border-0', 'text-bg-' + type)
	toast.appendChild(toastBody)

	if (!toastContainer) {
		toastContainer = document.createElement('div')
		toastContainer.classList.add('toast-container', 'position-fixed', 'start-0', 'bottom-0', 'p-3')

		document.body.appendChild(toastContainer)
	}

	toastContainer.appendChild(toast)

	slideUp(toast, 300)

	setTimeout(() => {
		slideDown(toast, 300)
	}, duration)
}