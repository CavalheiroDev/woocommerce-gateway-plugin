import InputLabel from "./InputLabel";
import InputHelper from "./InputHelper";
import {useState} from '@wordpress/element';


const InputCardNumber = ({hiddenId, inputLabelMessage, inputHelperMessage, helperVisibility, setHelperVisibility}) => {
    const setInputCardNumberMask = (event) => {
        const cardNumber = event.target.value
            .replace(/\s+/g, "")
            .replace(/[^0-9]/gi, "")
            .substr(0, 16);
        const parts = [];

        for (let i = 0; i < cardNumber.length; i += 4) {
            parts.push(cardNumber.substr(i, 4));
        }

        const formattedCardNumber = parts.length > 1 ? parts.join(" ") : event.target.value;

        event.target.value = formattedCardNumber;

        const hiddenInput = document.getElementById(hiddenId);
        if (hiddenInput) {
            hiddenInput.value = cardNumber
        }
    };

    const inputOnBlurHandler = (event) => {
        event.target.classList.remove('mp-focus');

        if (event.target.value === undefined || event.target.value === '') {
            event.target.classList.add('mp-error')
            setHelperVisibility(true);
        }
    }

    const inputOnFocusHandler = (event) => {
        event.target.classList.add('mp-focus');
        event.target.classList.remove('mp-error');
        setHelperVisibility(false);
    }

    return (
        <div className={'mp-checkout-custom-card-row'}>
            <InputLabel isOptional={false} message={inputLabelMessage}/>
            <input
                className={'mp-checkout-custom-card-input'}
                id={'form-checkout__cardNumber-container'}
                placeholder={'0000 0000 0000 0000'}
                style={{
                    fontSize: '16px',
                    height: '40px',
                    paddingLeft: '10px',
                }}
                onInput={setInputCardNumberMask}
                onBlur={inputOnBlurHandler}
                onFocus={inputOnFocusHandler}

            />
            <input type={'hidden'} name={'card-number-hidden'} id={hiddenId}/>

            <InputHelper isVisible={helperVisibility} message={inputHelperMessage}
                         inputId={'card-number-hidden-input-helper'}/>
        </div>
    )
};

export default InputCardNumber;