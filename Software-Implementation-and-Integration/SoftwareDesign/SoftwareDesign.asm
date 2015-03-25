@DATA
offset DS 1
stackPointer DS 1
outputs DS 12
state DS 1

@CODE
                     
                     TIMEMOTORDOWN EQU 3
                     BELT EQU 12000
                     BELTROUND EQU 20000
                     SORT EQU 8500
                     LENSLAMPPOSITION EQU 5
                     LENSLAMPSORTER EQU 6
                     HBRIDGE0 EQU 0
                     HBRIDGE1 EQU 1
                     CONVEYORBELT EQU 3
                     FEEDERENGINE EQU 7
                     DISPLAY EQU 8
                     LEDSTATEINDICATOR EQU 9
begin:               BRA main
                     
                     
                                                                  ;sleep
_timer:              MULS R5 10
                     PUSH R4
                     LOAD R4 R5
                     LOAD R5 -16
                     LOAD R5 [R5+13]
                     SUB  R5 R4
                     LOAD R4 -16
_wait:               CMP  R5 [R4+13]                              ; Compare the timer to 0
                     BMI  _wait
                     PULL R4
                     RTS
                     
_pressed:            PUSH R4                                      ;make sure all vars are the same at the end
                     PUSH R5
                     LOAD R4 R3
                     LOAD R5 2
                     BRS _pow
                     LOAD R3 R5
                     LOAD R5 -16
                     LOAD R4 [R5+7]
                     DIV R4 R3
                     MOD R4 2
                     
                     PUSH R4                                      ;the result
                     ADD SP 1                                     ;decrease the SP so we get the correct pulls
                     
                     PULL R5
                     PULL R4
                     
                     RTS
                     
_pow:                CMP R4 0
                     BEQ _pow1
                     CMP R4 1
                     BEQ _powR
                     PUSH R3
                     PUSH R4
                     SUB R4 1
                     LOAD R3 R5
_powLoop:            MULS R5 R3
                     SUB R4 1
                     CMP R4 0
                     BEQ _powReturn
                     BRA _powLoop
_powReturn:          PULL R4
                     PULL R3
                     RTS
_pow1:               LOAD R5 1
                     RTS
_powR:               RTS
                     
                                                                  ;display
_Hex7Seg:            BRS _Hex7Seg_bgn                             ; push address(tbl) onto stack and proceed at bgn
_Hex7Seg_tbl:        CONS %01111110                               ; 7-segment pattern for '0'
                     CONS %00110000                               ; 7-segment pattern for '1'
                     CONS %01101101                               ; 7-segment pattern for '2'
                     CONS %01111001                               ; 7-segment pattern for '3'
                     CONS %00110011                               ; 7-segment pattern for '4'
                     CONS %01011011                               ; 7-segment pattern for '5'
                     CONS %01011111                               ; 7-segment pattern for '6'
                     CONS %01110000                               ; 7-segment pattern for '7'
                     CONS %01111111                               ; 7-segment pattern for '8'
                     CONS %01111011                               ; 7-segment pattern for '9'
                     CONS %01110111                               ; 7-segment pattern for 'A'
                     CONS %00011111                               ; 7-segment pattern for 'b'
                     CONS %01001110                               ; 7-segment pattern for 'C'
                     CONS %00111101                               ; 7-segment pattern for 'd'
                     CONS %01001111                               ; 7-segment pattern for 'E'
                     CONS %01000111                               ; 7-segment pattern for 'F'
_Hex7Seg_bgn:        AND R5 %01111                                ; R0 = R0 MOD 16 , just to be safe...
                     LOAD R4 [SP++]                               ; R4 = address(tbl) (retrieve from stack)
                     LOAD R4 [R4+R5]                              ; R4 = tbl[R0]
                     LOAD R5 -16
                     STOR R4 [R5+8]                               ; and place this in the Display Element
                     RTS
main:                STOR R5 [GB +offset + 0]                     ;storeData(R5, 'offset', 0)
                     LOAD R0 timerInterrupt                       ;installCountdown('timerInterrupt')
                     ADD R0 R5
                     LOAD R1 16
                     STOR R0 [R1]
                     
                     LOAD R5 -16
                     
                                                                  ; Set the timer to 0
                     LOAD R0 0
                     SUB R0 [R5+13]
                     STOR R0 [R5+13]
                     STOR SP [GB +stackPointer + 0]               ;storeData(SP, 'stackPointer', 0)
                     LOAD R0 0                                    ;$counter = 0
                     LOAD R1 0                                    ;$temp = 0
                     STOR R1 [GB +outputs + HBRIDGE1]             ;storeData($temp, 'outputs', HBRIDGE1)
                     STOR R1 [GB +outputs + LENSLAMPPOSITION]     ;storeData($temp, 'outputs', LENSLAMPPOSITION)
                     STOR R1 [GB +outputs + LENSLAMPSORTER]       ;storeData($temp, 'outputs', LENSLAMPSORTER)
                     STOR R1 [GB +outputs + LEDSTATEINDICATOR]    ;storeData($temp, 'outputs', LEDSTATEINDICATOR)
                     STOR R1 [GB +outputs + DISPLAY]              ;storeData($temp, 'outputs', DISPLAY)
                     STOR R1 [GB +outputs + CONVEYORBELT]         ;storeData($temp, 'outputs', CONVEYORBELT)
                     STOR R1 [GB +outputs + FEEDERENGINE]         ;storeData($temp, 'outputs', FEEDERENGINE)
                     LOAD R2 0                                    ;$state = 0
                     STOR R2 [GB +state + 0]                      ;storeData($state, 'state', 0)
                     LOAD R1 9                                    ;$temp = 9
                     STOR R1 [GB +outputs + HBRIDGE0]             ;storeData($temp, 'outputs', HBRIDGE0)
                                                                  ;unset($temp, $state)
                     BRA initial                                  ;initial()
                     
timerInterrupt:      BRS timerManage                              ;timerManage()
                     LOAD R3 5                                    ;$temp = 5
                     PUSH R5                                      ;display($temp, "display", "")
                     PUSH R4
                     LOAD R5 R3
                     BRS _Hex7Seg
                     LOAD R4 %0000001
                     STOR R4 [R5+9]
                     PULL R4
                     PULL R5
                     LOAD R3 9                                    ;$temp = 9
                     STOR R3 [GB +outputs + HBRIDGE0]             ;storeData($temp, 'outputs', HBRIDGE0)
                     LOAD R3 0                                    ;$temp = 0
                     STOR R3 [GB +outputs + LENSLAMPPOSITION]     ;storeData($temp, 'outputs', LENSLAMPPOSITION)
                     STOR R3 [GB +outputs + LENSLAMPSORTER]       ;storeData($temp, 'outputs', LENSLAMPSORTER)
                     STOR R3 [GB +outputs + LEDSTATEINDICATOR]    ;storeData($temp, 'outputs', LEDSTATEINDICATOR)
                     STOR R3 [GB +outputs + DISPLAY]              ;storeData($temp, 'outputs', DISPLAY)
                     STOR R3 [GB +outputs + CONVEYORBELT]         ;storeData($temp, 'outputs', CONVEYORBELT)
                     STOR R3 [GB +outputs + FEEDERENGINE]         ;storeData($temp, 'outputs', FEEDERENGINE)
                     PUSH R5                                      ;display($temp, "display", "")
                     PUSH R4
                     LOAD R5 R3
                     BRS _Hex7Seg
                     LOAD R4 %0000001
                     STOR R4 [R5+9]
                     PULL R4
                     PULL R5
                                                                  ;unset($temp)
                     LOAD R1 [ GB + offset + 0 ]                  ;$temp = getData('offset', 0)
                     LOAD R3 initial                              ;$temp2 = getFuncLocation('initial')
                     ADD R1 R3                                    ;$temp += $temp2
                     ADD SP 2                                     ;addStackPointer(2)
                     PUSH R1                                      ;pushStack($temp)
                     ADD SP -1                                    ;addStackPointer(-1)
                     RTE
                     
initial:             LOAD R1 [ GB + stackPointer + 0 ]            ;$temp = getData('stackPointer', 0)
                     LOAD SP R1                                   ;setStackPointer($temp)
                     BRS timerManage                              ;timerManage()
                     PUSH R3                                      ;$push = getButtonPressed(5)
                     LOAD R3 5
                     BRS _pressed
                     PULL R3
                     SUB SP 5
                     PULL R2
                     ADD SP 4
                     CMP R2 1                                     ;if ($push == 1) {
                     BEQ conditional0
return0:                                                          ;unset($push)
                     BRA initial                                  ;initial()
                     
                                                                  ;if ($push == 1) {
conditional0:        LOAD R1 0                                    ;$temp = 0
                     STOR R1 [GB +outputs + HBRIDGE0]             ;storeData($temp, 'outputs', HBRIDGE0)
                     LOAD R1 9                                    ;$temp = 9
                     STOR R1 [GB +outputs + HBRIDGE1]             ;storeData($temp, 'outputs', HBRIDGE1)
                     LOAD R1 1                                    ;$temp = 1
                     STOR R1 [GB +state + 0]                      ;storeData($temp, 'state', 0)
                                                                  ;unset($temp)
                     LOAD R1 0                                    ;$sleep = 0
                     BRA calibrateSorter                          ;calibrateSorter()
                     
calibrateSorter:     BRS timerManage                              ;timerManage()
                     CMP R1 TIMEMOTORDOWN                         ;if ($sleep == TIMEMOTORDOWN) {
                     BEQ conditional1
return1:             ADD R1 1                                     ;$sleep+=1
                     BRA calibrateSorter                          ;calibrateSorter()
                     
                                                                  ;if ($sleep == TIMEMOTORDOWN) {
conditional1:        LOAD R2 0                                    ;$temp = 0
                     STOR R2 [GB +outputs + HBRIDGE1]             ;storeData($temp, 'outputs', HBRIDGE1)
                     LOAD R3 2                                    ;$state = 2
                     STOR R3 [GB +state + 0]                      ;storeData($state, 'state', 0)
                                                                  ;unset($state)
                     LOAD R1 0                                    ;$sleep = 0
                     BRA resting                                  ;resting()
                     
resting:                                                          ;unset ($sleep)
                     BRS timerManage                              ;timerManage()
                     PUSH R3                                      ;$startStop = getButtonPressed(0)
                     LOAD R3 0
                     BRS _pressed
                     PULL R3
                     SUB SP 5
                     PULL R1
                     ADD SP 4
                     CMP R1 1                                     ;if ($startStop == 1) {
                     BEQ conditional2
return2:                                                          ;unset($startStop)
                     BRA resting                                  ;resting()
                     
                                                                  ;if ($startStop == 1) {
conditional2:        PUSH R5                                      ;sleep(2000)
                     LOAD R5 2000
                     BRS _timer
                     PULL R5
                     LOAD R2 12                                   ;$temp = 12
                     STOR R2 [GB +outputs + LENSLAMPPOSITION]     ;storeData($temp, 'outputs', LENSLAMPPOSITION)
                     STOR R2 [GB +outputs + LENSLAMPSORTER]       ;storeData($temp, 'outputs', LENSLAMPSORTER)
                     LOAD R2 9                                    ;$temp = 9
                     STOR R2 [GB +outputs + CONVEYORBELT]         ;storeData($temp, 'outputs', CONVEYORBELT)
                     LOAD R2 5                                    ;$temp = 5
                     STOR R2 [GB +outputs + FEEDERENGINE]         ;storeData($temp, 'outputs', FEEDERENGINE)
                     PUSH R5 ;reset timer                         ;setCountdown(BELTROUND + BELT)
                     PUSH R4
                     LOAD R5 -16
                     LOAD R4 0
                     SUB R4 [R5+13]
                     STOR R4 [R5+13]                              ;set timer
                     LOAD R4 BELTROUND + BELT
                     STOR R4 [R5+13]
                     PULL R4
                     PULL R5
                     LOAD R3 3                                    ;$state = 3
                     STOR R3 [GB +state + 0]                      ;storeData($state, 'state', 0)
                                                                  ;unset($state)
                     BRA running                                  ;running()
                     
running:             BRS timerManage                              ;timerManage()
                     PUSH R3                                      ;$startStop = getButtonPressed(0)
                     LOAD R3 0
                     BRS _pressed
                     PULL R3
                     SUB SP 5
                     PULL R1
                     ADD SP 4
                     CMP R1 1                                     ;if ($startStop == 1) {
                     BEQ conditional3
return3:                                                          ;unset($startStop)
                     PUSH R3                                      ;$position = getButtonPressed(7)
                     LOAD R3 7
                     BRS _pressed
                     PULL R3
                     SUB SP 5
                     PULL R1
                     ADD SP 4
                     CMP R1 1                                     ;if ($position == 1) {
                     BEQ conditional4
return4:                                                          ;unset ($position)
                     BRA running                                  ;running()
                     
                                                                  ;if ($startStop == 1) {
conditional3:        LOAD R2 0                                    ;$temp = 0
                     STOR R2 [GB +outputs + FEEDERENGINE]         ;storeData($temp, 'outputs', FEEDERENGINE)
                                                                  ;unset($temp)
                     PUSH R5 ;reset timer                         ;setCountdown(BELT)
                     PUSH R4
                     LOAD R5 -16
                     LOAD R4 0
                     SUB R4 [R5+13]
                     STOR R4 [R5+13]                              ;set timer
                     LOAD R4 BELT
                     STOR R4 [R5+13]
                     PULL R4
                     PULL R5
                     LOAD R2 9                                    ;$state = 9
                     PUSH R5                                      ;display($state, "display", "")
                     PUSH R4
                     LOAD R5 R2
                     BRS _Hex7Seg
                     LOAD R4 %0000001
                     STOR R4 [R5+9]
                     PULL R4
                     PULL R5
                                                                  ;unset($state)
                     BRA runningTimer                             ;runningTimer()
                     
                                                                  ;if ($position == 1) {
conditional4:        PUSH R5 ;reset timer                         ;setCountdown(BELTROUND + BELT)
                     PUSH R4
                     LOAD R5 -16
                     LOAD R4 0
                     SUB R4 [R5+13]
                     STOR R4 [R5+13]                              ;set timer
                     LOAD R4 BELTROUND + BELT
                     STOR R4 [R5+13]
                     PULL R4
                     PULL R5
                     LOAD R2 4                                    ;$state = 4
                     PUSH R5                                      ;display($state, "display", "")
                     PUSH R4
                     LOAD R5 R2
                     BRS _Hex7Seg
                     LOAD R4 %0000001
                     STOR R4 [R5+9]
                     PULL R4
                     PULL R5
                                                                  ;unset($state)
                     BRA runningWait                              ;runningWait()
                     
runningWait:         BRS timerManage                              ;timerManage()
                     PUSH R3                                      ;$startStop = getButtonPressed(0)
                     LOAD R3 0
                     BRS _pressed
                     PULL R3
                     SUB SP 5
                     PULL R1
                     ADD SP 4
                     CMP R1 1                                     ;if ($startStop == 1) {
                     BEQ conditional5
return5:                                                          ;unset ($startStop)
                     PUSH R3                                      ;$position = getButtonPressed(7)
                     LOAD R3 7
                     BRS _pressed
                     PULL R3
                     SUB SP 5
                     PULL R1
                     ADD SP 4
                     CMP R1 1                                     ;if ($position == 1) {
                     BEQ conditional6
return6:                                                          ;unset ($position)
                     PUSH R3                                      ;$colour = getButtonPressed(6)
                     LOAD R3 6
                     BRS _pressed
                     PULL R3
                     SUB SP 5
                     PULL R1
                     ADD SP 4
                     CMP R1 1                                     ;if ($colour == 1) {
                     BEQ conditional7
return7:                                                          ;unset ($colour)
                     BRA runningWait                              ;runningWait()
                     
                                                                  ;if ($startStop == 1) {
conditional5:        LOAD R2 0                                    ;$temp = 0
                     STOR R2 [GB +outputs + FEEDERENGINE]         ;storeData($temp, 'outputs', FEEDERENGINE)
                                                                  ;unset($temp)
                     PUSH R5 ;reset timer                         ;setCountdown(BELT)
                     PUSH R4
                     LOAD R5 -16
                     LOAD R4 0
                     SUB R4 [R5+13]
                     STOR R4 [R5+13]                              ;set timer
                     LOAD R4 BELT
                     STOR R4 [R5+13]
                     PULL R4
                     PULL R5
                     LOAD R2 9                                    ;$state = 9
                     STOR R2 [GB +state + 0]                      ;storeData($state, 'state', 0)
                                                                  ;unset($state)
                     BRA runningTimer                             ;runningTimer()
                     
                                                                  ;if ($position == 1) {
conditional6:        PUSH R5 ;reset timer                         ;setCountdown(BELTROUND + BELT)
                     PUSH R4
                     LOAD R5 -16
                     LOAD R4 0
                     SUB R4 [R5+13]
                     STOR R4 [R5+13]                              ;set timer
                     LOAD R4 BELTROUND + BELT
                     STOR R4 [R5+13]
                     PULL R4
                     PULL R5
                     LOAD R2 5                                    ;$state = 5
                     PUSH R5                                      ;display($state, "display", "")
                     PUSH R4
                     LOAD R5 R2
                     BRS _Hex7Seg
                     LOAD R4 %0000001
                     STOR R4 [R5+9]
                     PULL R4
                     PULL R5
                                                                  ;unset ($state)
                     BRA runningTimerReset                        ;runningTimerReset()
                     
                                                                  ;if ($colour == 1) {
conditional7:        LOAD R2 9                                    ;$temp = 9
                     STOR R2 [GB +outputs + HBRIDGE0]             ;storeData($temp, 'outputs', HBRIDGE0)
                                                                  ;unset($temp)
                     LOAD R2 6                                    ;$state = 6
                     PUSH R5                                      ;display($state, "display", "")
                     PUSH R4
                     LOAD R5 R2
                     BRS _Hex7Seg
                     LOAD R4 %0000001
                     STOR R4 [R5+9]
                     PULL R4
                     PULL R5
                                                                  ;unset($state)
                     BRA motorUp                                  ;motorUp()
                     
motorUp:             BRS timerManage                              ;timerManage()
                     PUSH R3                                      ;$startStop = getButtonPressed(0)
                     LOAD R3 0
                     BRS _pressed
                     PULL R3
                     SUB SP 5
                     PULL R1
                     ADD SP 4
                     CMP R1 1                                     ;if ($startStop == 1) {
                     BEQ conditional8
return8:                                                          ;unset($startStop)
                     PUSH R3                                      ;$push = getButtonPressed(5)
                     LOAD R3 5
                     BRS _pressed
                     PULL R3
                     SUB SP 5
                     PULL R1
                     ADD SP 4
                     CMP R1 1                                     ;if ($push == 1) {
                     BEQ conditional9
return9:                                                          ;unset($push)
                     BRA motorUp                                  ;motorUp()
                     
                                                                  ;if ($startStop == 1) {
conditional8:        LOAD R2 0                                    ;$temp = 0
                     STOR R2 [GB +outputs + FEEDERENGINE]         ;storeData($temp, 'outputs', FEEDERENGINE)
                                                                  ;unset($temp)
                     PUSH R5 ;reset timer                         ;setCountdown(BELT)
                     PUSH R4
                     LOAD R5 -16
                     LOAD R4 0
                     SUB R4 [R5+13]
                     STOR R4 [R5+13]                              ;set timer
                     LOAD R4 BELT
                     STOR R4 [R5+13]
                     PULL R4
                     PULL R5
                     LOAD R2 10                                   ;$state = 10
                     PUSH R5                                      ;display($state, "display", "")
                     PUSH R4
                     LOAD R5 R2
                     BRS _Hex7Seg
                     LOAD R4 %0000001
                     STOR R4 [R5+9]
                     PULL R4
                     PULL R5
                                                                  ;unset($state)
                     BRA motorUpTimer                             ;motorUpTimer()
                     
                                                                  ;if ($push == 1) {
conditional9:        LOAD R2 0                                    ;$temp = 0
                     STOR R2 [GB +outputs + HBRIDGE0]             ;storeData($temp, 'outputs', HBRIDGE0)
                     LOAD R3 7                                    ;$state = 7
                     PUSH R5                                      ;display($state, "display", "")
                     PUSH R4
                     LOAD R5 R3
                     BRS _Hex7Seg
                     LOAD R4 %0000001
                     STOR R4 [R5+9]
                     PULL R4
                     PULL R5
                                                                  ;unset($state)
                     LOAD R3 0                                    ;$sleep = 0
                     BRA whiteWait                                ;whiteWait()
                     
whiteWait:           BRS timerManage                              ;timerManage()
                     CMP R3 SORT                                  ;if ($sleep == SORT) {
                     BEQ conditional10
return10:            PUSH R3                                      ;$startStop = getButtonPressed(0)
                     LOAD R3 0
                     BRS _pressed
                     PULL R3
                     SUB SP 5
                     PULL R1
                     ADD SP 4
                     CMP R1 1                                     ;if ($startStop == 1) {
                     BEQ conditional11
return11:                                                         ;unset($startStop)
                     ADD R3 1                                     ;$sleep+=1
                     BRA whiteWait                                ;whiteWait()
                     
                                                                  ;if ($sleep == SORT) {
conditional10:       LOAD R2 9                                    ;$temp = 9
                     STOR R1 [GB +state + 0]                      ;storeData($state, 'state', 0)
                                                                  ;unset($temp)
                     LOAD R1 8                                    ;$state = 8
                     PUSH R5                                      ;display($state, "display", "")
                     PUSH R4
                     LOAD R5 R1
                     BRS _Hex7Seg
                     LOAD R4 %0000001
                     STOR R4 [R5+9]
                     PULL R4
                     PULL R5
                                                                  ;unset($state)
                     LOAD R3 0                                    ;$sleep = 0
                     BRA motorDown                                ;motorDown()
                     
                                                                  ;if ($startStop == 1) {
conditional11:       LOAD R2 0                                    ;$temp = 0
                     STOR R2 [GB +outputs + FEEDERENGINE]         ;storeData($temp, 'outputs', FEEDERENGINE)
                                                                  ;unset($temp)
                     PUSH R5 ;reset timer                         ;setCountdown(BELT)
                     PUSH R4
                     LOAD R5 -16
                     LOAD R4 0
                     SUB R4 [R5+13]
                     STOR R4 [R5+13]                              ;set timer
                     LOAD R4 BELT
                     STOR R4 [R5+13]
                     PULL R4
                     PULL R5
                     LOAD R2 11                                   ;$state = 11
                     STOR R2 [GB +state + 0]                      ;storeData($state, 'state', 0)
                                                                  ;unset($state)
                     BRA whiteWaitTimer                           ;whiteWaitTimer()
                     
whiteWaitTimer:      BRS timerManage                              ;timerManage()
                     LOAD R1 15                                   ;$state = 15
                     STOR R1 [GB +state + 0]                      ;storeData($state, 'state', 0)
                                                                  ;unset($state)
                     BRA whiteWaitStop                            ;whiteWaitStop()
                     
whiteWaitStop:       BRS timerManage                              ;timerManage()
                     CMP R3 SORT                                  ;if ($sleep == SORT) {
                     BEQ conditional12
return12:            ADD R3 1                                     ;$sleep+=1
                     BRA whiteWaitStop                            ;whiteWaitStop()
                     
                                                                  ;if ($sleep == SORT) {
conditional12:       LOAD R1 9                                    ;$temp = 9
                     STOR R1 [GB +outputs + HBRIDGE1]             ;storeData($temp, 'outputs', HBRIDGE1)
                                                                  ;unset($temp)
                     LOAD R1 12                                   ;$state = 12
                     STOR R1 [GB +state + 0]                      ;storeData($state, 'state', 0)
                                                                  ;unset($state)
                     LOAD R3 0                                    ;$sleep = 0
                     BRA motorDownStop                            ;motorDownStop()
                     
motorDownStop:       BRS timerManage                              ;timerManage()
                     CMP R3 TIMEMOTORDOWN                         ;if ($sleep == TIMEMOTORDOWN) {
                     BEQ conditional13
return13:            ADD R3 1                                     ;$sleep+=1
                     BRA motorDownStop                            ;motorDownStop()
                     
                                                                  ;if ($sleep == TIMEMOTORDOWN) {
conditional13:       LOAD R1 0                                    ;$temp = 0
                     STOR R1 [GB +outputs + HBRIDGE1]             ;storeData($temp, 'outputs', HBRIDGE1)
                                                                  ;unset($temp)
                     LOAD R1 9                                    ;$state = 9
                     STOR R1 [GB +state + 0]                      ;storeData($state, 'state', 0)
                                                                  ;unset($state)
                     LOAD R3 0                                    ;$sleep = 0
                     BRA runningStop                              ;runningStop()
                     
runningStop:         BRS timerManage                              ;timerManage()
                     PUSH R3                                      ;$colour = getButtonPressed(6)
                     LOAD R3 6
                     BRS _pressed
                     PULL R3
                     SUB SP 5
                     PULL R1
                     ADD SP 4
                     CMP R1 1                                     ;if ($colour == 1) {
                     BEQ conditional14
return14:                                                         ;unset($colour)
                     BRA runningStop                              ;runningStop()
                     
                                                                  ;if ($colour == 1) {
conditional14:       LOAD R2 9                                    ;$temp = 9
                     STOR R2 [GB +outputs + HBRIDGE0]             ;storeData($temp, 'outputs', HBRIDGE0)
                                                                  ;unset($temp)
                     LOAD R2 10                                   ;$state = 10
                     STOR R2 [GB +state + 0]                      ;storeData($state, 'state', 0)
                                                                  ;unset($state)
                     BRA motorUpStop                              ;motorUpStop()
                     
motorUpStop:         BRS timerManage                              ;timerManage()
                     PUSH R3                                      ;$push = getButtonPressed(5)
                     LOAD R3 5
                     BRS _pressed
                     PULL R3
                     SUB SP 5
                     PULL R1
                     ADD SP 4
                     CMP R1 1                                     ;if ($push == 1) {
                     BEQ conditional15
return15:                                                         ;unset($push)
                     BRA motorUpStop                              ;motorUpStop()
                     
                                                                  ;if ($push == 1) {
conditional15:       LOAD R2 0                                    ;$temp = 0
                     STOR R2 [GB +outputs + HBRIDGE0]             ;storeData($temp, 'outputs', HBRIDGE0)
                                                                  ;unset ($temp)
                     LOAD R2 11                                   ;$state = 11
                     STOR R2 [GB +state + 0]                      ;storeData($state, 'state', 0)
                                                                  ;unset($state)
                     BRA whiteWaitStop                            ;whiteWaitStop()
                     
motorDown:           BRS timerManage                              ;timerManage()
                     CMP R3 TIMEMOTORDOWN                         ;if ($sleep == TIMEMOTORDOWN) {
                     BEQ conditional16
return16:            PUSH R3                                      ;$startStop = getButtonPressed(0)
                     LOAD R3 0
                     BRS _pressed
                     PULL R3
                     SUB SP 5
                     PULL R1
                     ADD SP 4
                     CMP R1 1                                     ;if ($startStop == 1) {
                     BEQ conditional17
return17:                                                         ;unset($startStop)
                     ADD R3 1                                     ;$sleep+=1
                     BRA motorDown                                ;motorDown()
                     
                                                                  ;if ($sleep == TIMEMOTORDOWN) {
conditional16:       LOAD R1 0                                    ;$temp = 0
                     STOR R1 [GB +outputs + HBRIDGE1]             ;storeData($temp, 'outputs', HBRIDGE1)
                                                                  ;unset($temp)
                     LOAD R1 4                                    ;$state = 4
                     STOR R1 [GB +state + 0]                      ;storeData($state, 'state', 0)
                     LOAD R3 0                                    ;$sleep = 0
                                                                  ;unset($state)
                     BRA runningWait                              ;runningWait()
                     
                                                                  ;if ($startStop == 1) {
conditional17:       LOAD R2 0                                    ;$temp = 0
                     STOR R2 [GB +outputs + FEEDERENGINE]         ;storeData($temp, 'outputs', FEEDERENGINE)
                                                                  ;unset($temp)
                     PUSH R5 ;reset timer                         ;setCountdown(BELT)
                     PUSH R4
                     LOAD R5 -16
                     LOAD R4 0
                     SUB R4 [R5+13]
                     STOR R4 [R5+13]                              ;set timer
                     LOAD R4 BELT
                     STOR R4 [R5+13]
                     PULL R4
                     PULL R5
                     LOAD R2 12                                   ;$state = 12
                     STOR R2 [GB +state + 0]                      ;storeData($state, 'state', 0)
                                                                  ;unset($state)
                     BRA motorDownTimer                           ;motorDownTimer()
                     
motorDownTimer:      BRS timerManage                              ;timerManage()
                     LOAD R1 16                                   ;$state = 16
                     STOR R1 [GB +state + 0]                      ;storeData($state, 'state', 0)
                                                                  ;unset($state)
                     BRA motorDownStop                            ;motorDownStop()
                     
motorUpTimer:        BRS timerManage                              ;timerManage()
                     LOAD R1 14                                   ;$state = 14
                     STOR R1 [GB +state + 0]                      ;storeData($state, 'state', 0)
                                                                  ;unset($state)
                     BRA motorUpStop                              ;motorUpStop()
                     
runningTimerReset:   BRS timerManage                              ;timerManage()
                     LOAD R1 4                                    ;$state = 4
                     STOR R1 [GB +state + 0]                      ;storeData($state, 'state', 0)
                                                                  ;unset($state)
                     BRA runningWait                              ;runningWait()
                     
runningTimer:        BRS timerManage                              ;timerManage()
                     LOAD R1 13                                   ;$state = 13
                     STOR R1 [GB +state + 0]                      ;storeData($state, 'state', 0)
                                                                  ;unset($state)
                     BRA runningStop                              ;runningStop()
                     
timerManage:         MOD R0 13                                    ;mod(13, $counter)
                     ADD R2 outputs                               ;$voltage = getData('outputs', $location)
                     LOAD R1 [ GB + R2]
                     SUB R2 outputs
                     CMP R1 R0                                    ;if ($voltage > $counter) {
                     BGT conditional18
return18:            CMP R2 7                                     ;if ($location > 7) {
                     BGT conditional19
return19:            ADD R2 1                                     ;$location+=1
                     BRA timerManage                              ;branch('timerManage')
                     
                                                                  ;if ($voltage > $counter) {
conditional18:       LOAD R1 R2                                   ;$voltage = $location
                     PUSH R4                                      ;$voltage = pow(2, $voltage)
                     PUSH R5
                     LOAD R4 R1
                     LOAD R5 2
                     BRS _pow
                     LOAD R1 R5
                     PULL R5
                     PULL R4
                     ADD R4 R1                                    ;$engines += $voltage
                     BRA return18                                 ;}
                     
                                                                  ;if ($location > 7) {
conditional19:       PUSH R5                                      ;sleep(100)
                     LOAD R5 100
                     BRS _timer
                     PULL R5
                     PUSH R5                                      ;display($engines, "leds", "")
                     LOAD R5 -16
                     STOR R4 [R5+11]
                     PULL R5
                     LOAD R4 0                                    ;$engines = 0
                     LOAD R2 0                                    ;$location = 0
                     ADD R0 1                                     ;$counter+=1
                                                                  ;unset($voltage)
                     PUSH R3                                      ;$abort = getButtonPressed(1)
                     LOAD R3 1
                     BRS _pressed
                     PULL R3
                     SUB SP 5
                     PULL R1
                     ADD SP 4
                     CMP R1 1                                     ;if ($abort == 1) {
                     BEQ conditional20
return20:                                                         ;unset($abort)
                     CMP R0 0                                     ;if ($counter == 0) {
                     BEQ conditional21
return21:            CMP R0 12                                    ;if ($counter == 12) {
                     BEQ conditional22
return22:            RTS                                          ;return
                     BRA return19                                 ;}
                     
                                                                  ;if ($abort == 1) {
conditional20:       BRA abort                                    ;abort()
                     BRA return20                                 ;}
                     
                                                                  ;if ($counter == 0) {
conditional21:       LOAD R1 [ GB + "state" + 0 ]                 ;$temp = getData("state", 0)
                     MOD R1 10                                    ;mod(10, $temp)
                     PUSH R5                                      ;display($temp, "display", "1")
                     PUSH R4
                     LOAD R5 R1
                     BRS _Hex7Seg
                     LOAD R4 %0000001
                     STOR R4 [R5+9]
                     PULL R4
                     PULL R5
                                                                  ;unset($temp)
                     BRA return21                                 ;}
                     
                                                                  ;if ($counter == 12) {
conditional22:       LOAD R1 [ GB + "state" + 0 ]                 ;$temp = getData("state", 0)
                     DIV R1 10                                    ;$temp /= 10
                     MOD R1 10                                    ;mod(10, $temp)
                     PUSH R5                                      ;display($temp, "display", "01")
                     PUSH R4
                     LOAD R5 R1
                     BRS _Hex7Seg
                     LOAD R4 %0000001
                     STOR R4 [R5+9]
                     PULL R4
                     PULL R5
                                                                  ;unset($temp)
                     BRA return22                                 ;}
                     
abort:                                                            ;unset($engines)
                                                                  ;unset($sleep)
                     LOAD R1 [ GB + stackPointer + 0 ]            ;$temp = getData('stackPointer', 0)
                     LOAD SP R1                                   ;setStackPointer($temp)
                     LOAD R1 0                                    ;$temp = 0
                     STOR R1 [GB +outputs + HBRIDGE1]             ;storeData($temp, 'outputs', HBRIDGE1)
                     STOR R1 [GB +outputs + HBRIDGE0]             ;storeData($temp, 'outputs', HBRIDGE0)
                     STOR R1 [GB +outputs + LENSLAMPPOSITION]     ;storeData($temp, 'outputs', LENSLAMPPOSITION)
                     STOR R1 [GB +outputs + LENSLAMPSORTER]       ;storeData($temp, 'outputs', LENSLAMPSORTER)
                     STOR R1 [GB +outputs + LEDSTATEINDICATOR]    ;storeData($temp, 'outputs', LEDSTATEINDICATOR)
                     STOR R1 [GB +outputs + DISPLAY]              ;storeData($temp, 'outputs', DISPLAY)
                     STOR R1 [GB +outputs + CONVEYORBELT]         ;storeData($temp, 'outputs', CONVEYORBELT)
                     STOR R1 [GB +outputs + FEEDERENGINE]         ;storeData($temp, 'outputs', FEEDERENGINE)
                                                                  ;unset($temp)
                     BRS timerManage                              ;timerManage()
                     LOAD R1 17                                   ;$state = 17
                     STOR R1 [GB +state + 0]                      ;storeData($state, 'state', 0)
                                                                  ;unset($state)
                     LOAD R1 7                                    ;$state = 7
                     STOR R1 [GB +state + 0]                      ;storeData($state, 'state', 0)
                                                                  ;unset($state)
                     BRA aborted                                  ;aborted()
                     
aborted:             PUSH R3                                      ;$startStop = getButtonPressed(0)
                     LOAD R3 0
                     BRS _pressed
                     PULL R3
                     SUB SP 5
                     PULL R1
                     ADD SP 4
                     CMP R1 1                                     ;if ($startStop == 1) {
                     BEQ conditional23
return23:                                                         ;unset($startStop)
                     BRA aborted                                  ;aborted()
                     
                                                                  ;if ($startStop == 1) {
conditional23:       LOAD R3 9                                    ;$temp = 9
                     STOR R3 [GB +outputs + HBRIDGE0]             ;storeData($temp, 'outputs', HBRIDGE0)
                     LOAD R4 0                                    ;$state = 0
                     STOR R4 [GB +state + 0]                      ;storeData($state, 'state', 0)
                                                                  ;unset($state)
                     BRA initial                                  ;initial()
                     
                     @END