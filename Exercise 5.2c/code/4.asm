@DATA
intensity DS 8
counter DS 1

@CODE
                    
                    WAIT EQU 1000
begin:              BRA main
                    
                    
_pow:               CMP R4 0
                    BEQ _pow1
                    CMP R4 1
                    BEQ _powR
                    PUSH R3
                    PUSH R4
                    SUB R4 1
                    LOAD R3 R5
_powLoop:           MULS R5 R3
                    SUB R4 1
                    CMP R4 0
                    BEQ _powReturn
                    BRA _powLoop
_powReturn:         PULL R4
                    PULL R3
                    RTS
_pow1:              LOAD R5 1
                    RTS
_powR:              RTS
                    
_pressed:           PUSH R4
                    LOAD R4 R3
                    LOAD R5 2
                    BRS _pow
                    LOAD R3 R5
                    LOAD R5 -16
                    LOAD R4 [R5+7]
                    DIV R4 R3
                    MOD R4 2
                    LOAD R5 R4
                    PULL R4
                    RTS
main:               LOAD R0 loop                    ;installCountdown('loop')
                    ADD R0 R5
                    LOAD R1 16
                    STOR R0 [R1]
                    
                    LOAD R5 -16
                    
                                                    ; Set the timer to 0
                    LOAD R0 0
                    SUB R0 [R5+13]
                    STOR R0 [R5+13]
                    LOAD R0 0                       ;$intensity = 0
                    LOAD R1 0                       ;$counter = 0
                    LOAD R2 0                       ;$location = 0
                    STOR R1 [GB +counter + 0]       ;_storeData($counter, 'counter', 0)
                    BRA init                        ;init()
                    
init:               ADD R2 1                        ;$location+=1
                    ADD R2 intensity                ;_storeData($intensity, 'intensity', $location)
                    STOR R0 [ GB + R2]
                    SUB R2 intensity
                    CMP R2 7                        ;if ($location == 7) {
                    BEQ conditional0
return0:            BRA init                        ;init()
                    BRA main
                    
                                                    ;if ($location == 7) {
conditional0:       LOAD R5 -16                     ;setTimer(WAIT)
                    LOAD R4 0
                    SUB R4 [R5+13]
                    STOR R4 [R5+13]
                    LOAD R4 WAIT
                    STOR R4 [R5+13]
                    SETI 8                          ;startCountdown()
                    BRA emptyLoop                   ;emptyLoop()
                    
emptyLoop:          BRA emptyLoop                   ;emptyLoop()
                    BRA main
                    
loop:               LOAD R5 -16                     ;setTimer(WAIT)
                    LOAD R4 0
                    SUB R4 [R5+13]
                    STOR R4 [R5+13]
                    LOAD R4 WAIT
                    STOR R4 [R5+13]
                    SETI 8                          ;startCountdown()
                    LOAD R1 [ GB + counter + 0 ]    ;$counter = _getData('counter', 0)
                    ADD R1 1                        ;$counter+=1
                    CMP R1 1000                     ;if ($counter == 1000) {
                    BEQ conditional1
return1:            STOR R1 [GB +counter + 0]       ;_storeData($counter, 'counter', 0)
                    LOAD R2 -1                      ;$location = -1
                    LOAD R3 0                       ;$lights = 0
                    LOAD R4 0                       ;$temp = 0
                    BRA getValues                   ;getValues()
                    RTE
                    
                                                    ;if ($counter == 1000) {
conditional1:       LOAD R1 1                       ;$counter = 1
                    BRA return1                     ;}
                    
getValues:          ADD R2 1                        ;$location+=1
                    CMP R2 0                        ;if ($location == 0) {
                    BEQ conditional2
return2:            CMP R2 0                        ;if ($location != 0) {
                    BNE conditional3
return3:            MOD R1 10                       ;modulo($counter,10)
                    CMP R1 R4                       ;if ($counter < $temp) {
                    BMI conditional4
return4:            LOAD R1 [ GB + counter + 0 ]    ;$counter = _getData('counter', 0)
                    CMP R2 7                        ;if ($location == 7) {
                    BEQ conditional5
return5:            BRA getValues                   ;getValues()
                    BRA main
                    
                                                    ;if ($location == 0) {
conditional2:       LOAD R5 -16                     ;getInput($temp, 'analog')
                    LOAD R4 [R5 + 6]
                    DIV R4 25                       ;$temp /= 25
                    BRA return2                     ;}
                    
                                                    ;if ($location != 0) {
conditional3:       ADD R2 intensity                ;$temp = _getData('intensity', $location)
                    LOAD R4 [ GB + R2]
                    SUB R2 intensity
                    BRA return3                     ;}
                    
                                                    ;if ($counter < $temp) {
conditional4:       PUSH R3                         ;stackPush($lights)
                    LOAD R4 R2                      ;pow(2, $location)
                    LOAD R5 2
                    BRS _pow
                    PULL R3                         ;stackPull($lights)
                    ADD R3 R5                       ;$lights += R5
                    BRA return4                     ;}
                    
                                                    ;if ($location == 7) {
conditional5:       LOAD R5 -16                     ;display($lights, 'leds')
                    LOAD R4 R3
                    STOR R4 [R5+11]
                    LOAD R2 0                       ;$location = 0
                    BRA checkButtons                ;checkButtons()
                    
checkButtons:       CMP R1 1                        ;if ($counter != 1) {
                    BNE conditional6
return6:            ADD R2 1                        ;$location+=1
                    PUSH R3                         ;buttonPressed($location)
                    LOAD R3 R2
                    BRS _pressed
                    PULL R3
                    CMP R5 1                        ;if (R5 == 1) {
                    BEQ conditional7
return7:            CMP R2 7                        ;if ($location != 7) {
                    BNE conditional11
return11:           RTE                             ;returnt
                    BRA main
                    
                                                    ;if ($counter != 1) {
conditional6:       RTE                             ;returnt
                    BRA return6                     ;}
                    
                                                    ;if (R5 == 1) {
conditional7:       ADD R2 intensity                ;$temp = _getData('intensity', $location)
                    LOAD R4 [ GB + R2]
                    SUB R2 intensity
                    ADD R4 1                        ;$temp+=1
                    PUSH R3                         ;buttonPressed(0)
                    LOAD R3 0
                    BRS _pressed
                    PULL R3
                    CMP R5 1                        ;if (R5 == 1) {
                    BEQ conditional8
return8:            CMP R4 11                       ;if ($temp != 11) {
                    BNE conditional9
return9:            BRA return7                     ;}
                    BRA main
                    
                                                    ;if (R5 == 1) {
conditional8:       SUB R4 2                        ;$temp -= 2
                    BRA return8                     ;}
                    
                                                    ;if ($temp != 11) {
conditional9:       CMP R4 -1                       ;if ($temp != -1) {
                    BNE conditional10
return10:           BRA return9                     ;}
                    BRA main
                    
                                                    ;if ($temp != -1) {
conditional10:      ADD R2 intensity                ;_storeData($temp, 'intensity', $location)
                    STOR R4 [ GB + R2]
                    SUB R2 intensity
                    BRA return10                    ;}
                    
                                                    ;if ($location != 7) {
conditional11:      BRA checkButtons                ;checkButtons()
                    BRA return11                    ;}
                    
                    @END