import InputLabel from "./InputLabel";
import InputHelper from "./InputHelper";
import {useState} from "@wordpress/element";

const InputCardExpirationDate = ({inputLabelMessage, placeholder, helperVisibility, setHelperVisibility}) => {
    const [helperMessage, setHelperMessage] = useState('Dado obrigatório');

    const setExpirationDateMark = (event) => {
        let inputChar = String.fromCharCode(event.keyCode);
        let code = event.keyCode;
        let allowedKeys = [8];
        if (allowedKeys.indexOf(code) !== -1) {
            return;
        }

        const formattedValue = event.target.value.replace(
            /^([1-9]\/|[2-9])$/g, '0$1/'
        ).replace(
            /^(0[1-9]|1[0-2])$/g, '$1/'
        ).replace(
            /^([0-1])([3-9])$/g, '0$1/$2'
        ).replace(
            /^(0?[1-9]|1[0-2])([0-9]{2})$/g, '$1/$2'
        ).replace(
            /^([0]+)\/|[0]+$/g, '0'
        ).replace(
            /[^\d\/]|^[\/]*$/g, ''
        ).replace(
            /\/\//g, '/'
        );
        event.target.value = formattedValue;
    };

    const validateExpirationDate = (inputMonth, inputYear) => {
        const now = new Date();
        const currentYear = now.getFullYear() - 2000;

        if (parseInt(inputYear) < currentYear) {
            setHelperMessage('Data inválida')
            setHelperVisibility(true);
            return false;
        }

        const currentMonth = now.getMonth() + 1;

        if (parseInt(inputMonth) < currentMonth && parseInt(inputYear) === currentYear) {
            setHelperMessage('Data inválida')
            setHelperVisibility(true);
            return false;
        }

        return true;
    }

    const injectExpirationDateInHidden = (inputMonth, inputYear) => {
        const monthHidden = document.getElementsByName('card-expiry-month-hidden')[0];
        if (monthHidden) {
            monthHidden.value = inputMonth;
        }

        const yearHidden = document.getElementsByName('card-expiry-year-hidden')[0];
        if (yearHidden) {
            yearHidden.value = inputYear;
        }
    }

    const inputOnBlurHandler = (event) => {
        event.target.classList.remove('mp-focus');

        if (event.target.value === '' || event.target.value === undefined) {
            setHelperMessage('Dado obrigatório');
            event.target.classList.add('mp-error');
            setHelperVisibility(true);

        } else {
            let regExp = /(1[0-2]|0[1-9]|\d)\/(20\d{2}|19\d{2}|0(?!0)\d|[1-9]\d)/;
            let matches = regExp.exec(event.target.value);

            let isValid = validateExpirationDate(matches[1], matches[2]);
            if (!isValid) {
                event.target.classList.add('mp-error');
            }

            injectExpirationDateInHidden(matches[1], matches[2])

        }
    };

    const inputOnFocusHandler = (event) => {
        event.target.classList.add('mp-focus');
        event.target.classList.remove('mp-error');

        setHelperVisibility(false);
    }

    return (
        <div className={'mp-checkout-custom-card-column'}>
            <InputLabel message={inputLabelMessage} isOptional={false}/>

            <input
                id={'form-checkout__expirationDate-container'}
                className={'mp-checkout-custom-card-input mp-checkout-custom-left-card-input'}
                maxLength={5}
                placeholder={placeholder}
                style={{
                    fontSize: '16px',
                    height: '40px',
                    paddingLeft: '10px'
                }}
                type={'text'}
                onKeyUp={setExpirationDateMark}
                onFocus={inputOnFocusHandler}
                onBlur={inputOnBlurHandler}
            />
            <input type="hidden" name="card-expiry-month-hidden" id={'card-expiry-month-hidden'}/>
            <input type="hidden" name="card-expiry-year-hidden" id={'card-expiry-year-hidden'}/>

            <InputHelper
                isVisible={helperVisibility}
                message={helperMessage}
                inputId={'card-expiry-month-hidden-helper'}
            />
        </div>
    )

};

export default InputCardExpirationDate;