import InputLabel from "./InputLabel";
import InputHelper from "./InputHelper";


const InputHolderName = ({helperVisibility, setHelperVisibility}) => {

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
        <div className={'mp-checkout-custom-card-row'} id={'mp-card-holder-div'}>
            <InputLabel message={'Nome do títular como aparece no cartão'} isOptional={false}/>

            <input
                className={'mp-checkout-custom-card-input mp-card-holder-name'}
                placeholder={'Ex.: Leonardo Ribeiro'}
                id={'card-holder-name'}
                name={'mp-card-holder-name'}
                data-checkout={'cardholderName'}
                onFocus={inputOnFocusHandler}
                onBlur={inputOnBlurHandler}
            />

            <InputHelper
                isVisible={helperVisibility}
                message={'Dado obrigatório'}
                inputId={'card-holder-name-helper'}
            />
        </div>
    )
};

export default InputHolderName;