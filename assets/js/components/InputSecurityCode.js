import InputLabel from "./InputLabel";
import InputHelper from "./InputHelper";
import {useState} from "@wordpress/element";

const InputSecurityCode = ({helperVisibility, setHelperVisibility}) => {
    const inputOnFocusHandler = (event) => {
        event.target.classList.add('mp-focus');
        event.target.classList.remove('mp-error');

        setHelperVisibility(false);
    }

    const inputOnBlurHandler = (event) => {
        event.target.classList.remove('mp-focus');

        if (event.target.value === undefined || event.target.value === '') {
            event.target.classList.add('mp-error');
            setHelperVisibility(true);
        }
    }

    return (
        <div className={'mp-checkout-custom-card-column'}>
            <InputLabel message={'Código de segurança'} isOptional={false}/>

            <input
                id={'card-security-code'}
                className={'mp-checkout-custom-card-input'}
                placeholder={'123'}
                style={{
                    fontSize: '16px',
                    height: '40px',
                    paddingLeft: '10px'
                }}
                maxLength={3}
                onFocus={inputOnFocusHandler}
                onBlur={inputOnBlurHandler}
            />

            <p id={'mp-security-code-info'} className={'mp-checkout-custom-info-text'}/>
            <InputHelper
                isVisible={helperVisibility}
                message={'Dado obrigatório'}
                inputId={'card-security-code-helper'}
            />
        </div>
    )
}

export default InputSecurityCode;