@DATA
offset DS 1
stackPointer DS 1

@CODE
                    
                    TIMEMOTORDOWN EQU 30
                    BELT EQU 1200
                    SORT EQU 850
                    LENSLAMPPOSITION EQU 5
                    LENSLAMPSORTER EQU 6
                    HBRIDGE0 EQU 0
                    HBRIDGE1 EQU 1
                    CONVEYORBELT EQU 3
                    FEEDERENGINE EQU 7
                    DISPLAY EQU 8
                    LEDSTATEINDICATOR EQU 9
begin:              BRA main
                    
                    
                                                         ;sleep
_timer:             MULS R5 10
                    PUSH R4
                    LOAD R4 R5
                    LOAD R5 -16
                    LOAD R5 [R5+13]
                    SUB  R5 R4
                    LOAD R4 -16
_wait:              CMP  R5 [R4+13]                      ; Compare the timer to 0
                    BMI  _wait
                    PULL R4
                    RTS
main:               STOR R5 [GB +offset + 0]             ;storeData(R5,'offset',0)
                    LOAD R0 interrupt                    ;installCountdown('interrupt')
                    ADD R0 R5
                    LOAD R1 16
                    STOR R0 [R1]
                    
                    LOAD R5 -16
                    
                                                         ; Set the timer to 0
                    LOAD R0 0
                    SUB R0 [R5+13]
                    STOR R0 [R5+13]
                    STOR SP [GB +stackPointer + 0]       ;storeData(SP,'stackPointer',0)
                    LOAD R0 0                            ;$temp = 0
                    PUSH R5                              ;display($temp, 'leds2', '')
                    LOAD R5 -16
                    STOR R0 [R5+10]
                    PULL R5
                    LOAD R1 0                            ;$counter = 0
                    PUSH R5 ;reset timer                 ;setCountdown(2000)
                    PUSH R4
                    LOAD R5 -16
                    LOAD R4 0
                    SUB R4 [R5+13]
                    STOR R4 [R5+13]                      ;set timer
                    LOAD R4 2000
                    STOR R4 [R5+13]
                    PULL R4
                    PULL R5
                    LOAD R0 93492304                     ;$temp=93492304
                    PUSH R0                              ;pushStack($temp)
                    PUSH R0                              ;pushStack($temp)
                    PUSH R0                              ;pushStack($temp)
                    PUSH R0                              ;pushStack($temp)
                    PUSH R0                              ;pushStack($temp)
                    PUSH R0                              ;pushStack($temp)
                    PUSH R0                              ;pushStack($temp)
                    PUSH R0                              ;pushStack($temp)
                    BRA init                             ;init()
                    
interrupt:          LOAD R0 5                            ;$temp = 5
                    PUSH R5                              ;display($temp, 'leds2', '')
                    LOAD R5 -16
                    STOR R0 [R5+10]
                    PULL R5
                    PUSH R5                              ;sleep(1000)
                    LOAD R5 1000
                    BRS _timer
                    PULL R5
                    LOAD R0 0                            ;$temp = 0
                    PUSH R5                              ;display($temp, 'leds2', '')
                    LOAD R5 -16
                    STOR R0 [R5+10]
                    PULL R5
                    PUSH R5 ;reset timer                 ;setCountdown(2000)
                    PUSH R4
                    LOAD R5 -16
                    LOAD R4 0
                    SUB R4 [R5+13]
                    STOR R4 [R5+13]                      ;set timer
                    LOAD R4 2000
                    STOR R4 [R5+13]
                    PULL R4
                    PULL R5
                    SETI 8                               ;startCountdown()
                    LOAD R0 [ GB + offset + 0 ]          ;$temp=getData('offset',0)
                    LOAD R2 init                         ;$temp2=getFuncLocation('init')
                    ADD R0 R2                            ;$temp+=$temp2
                    ADD SP 2                             ;addStackPointer(2)
                    PUSH R0                              ;pushStack($temp)
                    ADD SP -1                            ;addStackPointer(-1)
                    RTE                                  ;returnt
                    RTE
                    
init:               LOAD R0 [ GB + stackPointer + 0 ]    ;$temp=getData('stackPointer',0)
                    LOAD SP R0                           ;setStackPointer($temp)
                    LOAD R0 0                            ;$temp=0
                    ADD R0 1                             ;$temp+=1
                    PUSH R0                              ;pushStack($temp)
                    ADD R0 1                             ;$temp+=1
                    PUSH R0                              ;pushStack($temp)
                    ADD R0 1                             ;$temp+=1
                    PUSH R0                              ;pushStack($temp)
                    ADD R0 1                             ;$temp+=1
                    PUSH R0                              ;pushStack($temp)
                    ADD R0 1                             ;$temp+=1
                    PUSH R0                              ;pushStack($temp)
                    BRA init                             ;init()
                    
                    @END