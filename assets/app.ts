import './modules/collections'
import './modules/selects'
import './modules/toasts'
import './modules/tooltips'

import './prototypes/Button'
import './prototypes/Element'
import './prototypes/Number'

import './functions/Dom'
import './functions/Event'
import './functions/Form'
import './functions/slideDown'
import './functions/slideUp'
import './functions/ToastColor'

globalThis.base = (document.querySelector('meta[name="base"]') as HTMLMetaElement)?.content