import { html } from './Dom'
import slideDown from './slideDown'
import slideUp from './slideUp'

export enum ToastType {
	Success = 'success',
	Error = 'danger',
	Warning = 'warning',
	Info = 'info',
}

enum ToastIcon {
	'success' = 'check',
	'danger' = 'xmark',
	'warning' = 'exclamation',
	'info' = 'info',
}

let toastContainer = document.querySelector('.toast-container')

export default function displayToast(type: ToastType, text: string, duration: number = 10000) {
	const toastString = '' +
		'<div class="toast align-items-center text-bg-' + type +' border-0 show" role="alert" aria-live="assertive" aria-atomic="true">' +
		'    <div class="d-flex">' +
		'        <div class="toast-body">' +
		'            <div class="d-flex align-items-center float-start h-100">' +
		'                <span class="fa-stack me-2">' +
		'                    <i class="fa-solid fa-badge fa-stack-2x"></i>' +
		'                    <i class="fa-solid fa-' + ToastIcon[type] + ' fa-stack-1x text-' + type + '"></i>' +
		'                </span>' +
		'            </div>' +
		'            <span class="d-flex align-items-center h-100">' + text + '</span>' +
		'        </div>' +
		'    </div>' +
		'</div>'

	const toast = html(toastString)

	if (!toastContainer) {
		toastContainer = document.createElement('div')
		toastContainer.classList.add('toast-container', 'position-fixed', 'end-0', 'bottom-0', 'p-3')

		document.body.appendChild(toastContainer)
	}

	const onClick = () => {
		slideDown(toast, 300)

		toast.removeEventListener('click', onClick)
	}

	toast.addEventListener('click', onClick)

	toastContainer.appendChild(toast)

	slideUp(toast, 300)

	if (duration) {
		setTimeout(onClick, duration)
	}
}
