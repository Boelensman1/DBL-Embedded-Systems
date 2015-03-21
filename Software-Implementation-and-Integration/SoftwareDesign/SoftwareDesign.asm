@DATA
outputs DS 12

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
begin:               BRA main
                     
                     
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
main:                LOAD R0 0                                    ;$counter=0
                     LOAD R1 0                                    ;$temp = 0
                     STOR R1 [GB +outputs + HBRIDGE1]             ;_storeData($temp, 'outputs', HBRIDGE1)
                     STOR R1 [GB +outputs + HBRIDGE0]             ;_storeData($temp, 'outputs', HBRIDGE0)
                     STOR R1 [GB +outputs + LENSLAMPPOSITION]     ;_storeData($temp, 'outputs', LENSLAMPPOSITION)
                     STOR R1 [GB +outputs + LENSLAMPSORTER]       ;_storeData($temp, 'outputs', LENSLAMPSORTER)
                     STOR R1 [GB +outputs + LEDSTATEINDICATOR]    ;_storeData($temp, 'outputs', LEDSTATEINDICATOR)
                     STOR R1 [GB +outputs + DISPLAY]              ;_storeData($temp, 'outputs', DISPLAY)
                     STOR R1 [GB +outputs + CONVEYORBELT]         ;_storeData($temp, 'outputs', CONVEYORBELT)
                     STOR R1 [GB +outputs + FEEDERENGINE]         ;_storeData($temp, 'outputs', FEEDERENGINE)
                     LOAD R2 0                                    ;$state = 0
                     LOAD R5 -16                                  ;display($state, "leds2", "")
                     LOAD R4 R2
                     STOR R4 [R5+10]
                     LOAD R1 9                                    ;$temp = 9
                     STOR R1 [GB +outputs + HBRIDGE0]             ;_storeData($temp, 'outputs', HBRIDGE0)
                                                                  ;unset($temp, $state)
                     BRA initial                                  ;initial()
                     
initial:             BRS timerManage                              ;timerManage()
                     PUSH R3                                      ;$push = _getButtonPressed(5)
                     LOAD R3 5
                     BRS _pressed
                     PULL R4
                     SUB SP 5
                     PULL R1
                     ADD SP 4
                     CMP R1 1                                     ;if ($push == 1) {
                     BEQ conditional0
return0:             BRA initial                                  ;initial()
                     BRA main
                     
                                                                  ;if ($push == 1) {
conditional0:        LOAD R2 9                                    ;$temp = 9
                     STOR R2 [GB +outputs + HBRIDGE0]             ;_storeData($temp, 'outputs', HBRIDGE0)
                     LOAD R2 0                                    ;$temp = 0
                     STOR R2 [GB +outputs + HBRIDGE1]             ;_storeData($temp, 'outputs', HBRIDGE1)
                     LOAD R3 1                                    ;$state = 1
                     LOAD R5 -16                                  ;display($state, "leds2", "")
                     LOAD R4 R3
                     STOR R4 [R5+10]
                                                                  ;unset($state, $push)
                     LOAD R1 0                                    ;$sleep = 0
                     BRA calibrateSorter                          ;calibrateSorter()
                     
calibrateSorter:     BRS timerManage                              ;timerManage()
                     CMP R1 TIMEMOTORDOWN                         ;if ($sleep == TIMEMOTORDOWN) {
                     BEQ conditional1
return1:             ADD R1 1                                     ;$sleep+=1
                     BRA calibrateSorter                          ;calibrateSorter()
                     
                                                                  ;if ($sleep == TIMEMOTORDOWN) {
conditional1:        LOAD R2 9                                    ;$temp = 9
                     STOR R2 [GB +outputs + HBRIDGE1]             ;_storeData($temp, 'outputs', HBRIDGE1)
                     LOAD R3 2                                    ;$state = 2
                     LOAD R5 -16                                  ;display($state, "leds", "")
                     LOAD R4 R3
                     STOR R4 [R5+11]
                     LOAD R1 0                                    ;$sleep = 0
                                                                  ;unset($state)
                     BRA resting                                  ;resting()
                     
resting:                                                          ;unset ($sleep)
                     BRS timerManage                              ;timerManage()
                     PUSH R3                                      ;$startStop = _getButtonPressed(0)
                     LOAD R3 0
                     BRS _pressed
                     PULL R4
                     SUB SP 5
                     PULL R1
                     ADD SP 4
                     CMP R1 1                                     ;if ($startStop == 1) {
                     BEQ conditional2
return2:             BRA resting                                  ;resting()
                     BRA main
                     
                                                                  ;if ($startStop == 1) {
conditional2:        LOAD R2 12                                   ;$temp = 12
                     STOR R2 [GB +outputs + LENSLAMPPOSITION]     ;_storeData($temp, 'outputs', LENSLAMPPOSITION)
                     STOR R2 [GB +outputs + LENSLAMPSORTER]       ;_storeData($temp, 'outputs', LENSLAMPSORTER)
                     LOAD R2 9                                    ;$temp = 9
                     STOR R2 [GB +outputs + CONVEYORBELT]         ;_storeData($temp, 'outputs', CONVEYORBELT)
                     LOAD R2 5                                    ;$temp = 5
                     STOR R2 [GB +outputs + FEEDERENGINE]         ;_storeData($temp, 'outputs', FEEDERENGINE)
                     LOAD R5 -16                                  ;setTimer(2 + BELT)
                     LOAD R4 0
                     SUB R4 [R5+13]
                     STOR R4 [R5+13]
                     LOAD R4 2 + BELT
                     STOR R4 [R5+13]
                     LOAD R3 3                                    ;$state = 3
                     LOAD R5 -16                                  ;display($state, "leds2", "")
                     LOAD R4 R3
                     STOR R4 [R5+10]
                                                                  ;unset($startStop, $state)
                     BRA running                                  ;running()
                     
running:             BRS timerManage                              ;timerManage()
                     PUSH R3                                      ;$startStop = _getButtonPressed(0)
                     LOAD R3 0
                     BRS _pressed
                     PULL R4
                     SUB SP 5
                     PULL R1
                     ADD SP 4
                     CMP R1 1                                     ;if ($startStop == 1) {
                     BEQ conditional3
return3:                                                          ;unset($startStop)
                     PUSH R3                                      ;$position = _getButtonPressed(7)
                     LOAD R3 7
                     BRS _pressed
                     PULL R4
                     SUB SP 5
                     PULL R1
                     ADD SP 4
                     CMP R1 1                                     ;if ($position == 1) {
                     BEQ conditional4
return4:             BRA running                                  ;running()
                     BRA main
                     
                                                                  ;if ($startStop == 1) {
conditional3:        LOAD R2 0                                    ;$temp = 0
                     STOR R2 [GB +outputs + FEEDERENGINE]         ;_storeData($temp, 'outputs', FEEDERENGINE)
                     LOAD R5 -16                                  ;setTimer(BELT)
                     LOAD R4 0
                     SUB R4 [R5+13]
                     STOR R4 [R5+13]
                     LOAD R4 BELT
                     STOR R4 [R5+13]
                     LOAD R3 9                                    ;$state = 9
                     LOAD R5 -16                                  ;display($state, "leds2", "")
                     LOAD R4 R3
                     STOR R4 [R5+10]
                                                                  ;unset($state, $temp)
                     BRA runningTimer                             ;runningTimer()
                     
                                                                  ;if ($position == 1) {
conditional4:        LOAD R5 -16                                  ;setTimer(2 + BELT)
                     LOAD R4 0
                     SUB R4 [R5+13]
                     STOR R4 [R5+13]
                     LOAD R4 2 + BELT
                     STOR R4 [R5+13]
                     LOAD R2 4                                    ;$state = 4
                     LOAD R5 -16                                  ;display($state, "leds2", "")
                     LOAD R4 R2
                     STOR R4 [R5+10]
                                                                  ;unset($state)
                     BRA runningWait                              ;runningWait()
                     
runningWait:         BRS timerManage                              ;timerManage()
                     PUSH R3                                      ;$position = _getButtonPressed(7)
                     LOAD R3 7
                     BRS _pressed
                     PULL R4
                     SUB SP 5
                     PULL R1
                     ADD SP 4
                     PUSH R3                                      ;$colour = _getButtonPressed(6)
                     LOAD R3 6
                     BRS _pressed
                     PULL R4
                     SUB SP 5
                     PULL R2
                     ADD SP 4
                     PUSH R3                                      ;$startStop = _getButtonPressed(0)
                     LOAD R3 0
                     BRS _pressed
                     PULL R4
                     SUB SP 5
                     PULL R3
                     ADD SP 4
                     CMP R3 1                                     ;if ($startStop == 1) {
                     BEQ conditional5
return5:             CMP R1 1                                     ;if ($position == 1) {
                     BEQ conditional6
return6:             CMP R3 1                                     ;if ($colour == 1) {
                     BEQ conditional7
return7:             BRA runningWait                              ;runningWait()
                     BRA main
                     
                                                                  ;if ($startStop == 1) {
conditional5:        LOAD R4 0                                    ;$temp = 0
                     STOR R4 [GB +outputs + FEEDERENGINE]         ;_storeData($temp, 'outputs', FEEDERENGINE)
                     LOAD R5 -16                                  ;setTimer(BELT)
                     LOAD R4 0
                     SUB R4 [R5+13]
                     STOR R4 [R5+13]
                     LOAD R4 BELT
                     STOR R4 [R5+13]
                                                                  ;unset($colour)
                     LOAD R2 9                                    ;$state = 9
                     LOAD R5 -16                                  ;display($state, "leds2", "")
                     LOAD R4 R2
                     STOR R4 [R5+10]
                                                                  ;unset($position, $startStop, $state)
                     BRA runningTimer                             ;runningTimer()
                     
                                                                  ;if ($position == 1) {
conditional6:        LOAD R5 -16                                  ;setTimer(2 + BELT)
                     LOAD R4 0
                     SUB R4 [R5+13]
                     STOR R4 [R5+13]
                     LOAD R4 2 + BELT
                     STOR R4 [R5+13]
                     LOAD R2 5                                    ;$state = 5
                     LOAD R5 -16                                  ;display($state, "leds2", "")
                     LOAD R4 R2
                     STOR R4 [R5+10]
                     BRA runningTimerReset                        ;runningTimerReset()
                     
                                                                  ;if ($colour == 1) {
conditional7:        LOAD R4 9                                    ;$temp = 9
                     STOR R4 [GB +outputs + HBRIDGE0]             ;_storeData($temp, 'outputs', HBRIDGE0)
                     LOAD R5 -16                                  ;setTimer(SORT)
                     LOAD R4 0
                     SUB R4 [R5+13]
                     STOR R4 [R5+13]
                     LOAD R4 SORT
                     STOR R4 [R5+13]
                     LOAD R2 6                                    ;$state = 6
                     LOAD R5 -16                                  ;display($state, "leds2", "")
                     LOAD R4 R2
                     STOR R4 [R5+10]
                                                                  ;unset($position, $state)
                     BRA motorUp                                  ;motorUp()
                     
motorUp:             BRS timerManage                              ;timerManage()
                     PUSH R3                                      ;$push = _getButtonPressed(7)
                     LOAD R3 7
                     BRS _pressed
                     PULL R4
                     SUB SP 5
                     PULL R1
                     ADD SP 4
                     PUSH R3                                      ;$startStop = _getButtonPressed(0)
                     LOAD R3 0
                     BRS _pressed
                     PULL R4
                     SUB SP 5
                     PULL R2
                     ADD SP 4
                     CMP R2 1                                     ;if ($startStop == 1) {
                     BEQ conditional8
return8:             CMP R1 1                                     ;if ($push == 1) {
                     BEQ conditional9
return9:             BRA main
                     
                                                                  ;if ($startStop == 1) {
conditional8:        LOAD R4 0                                    ;$temp = 0
                     STOR R4 [GB +outputs + FEEDERENGINE]         ;_storeData($temp, 'outputs', FEEDERENGINE)
                     LOAD R5 -16                                  ;setTimer(BELT)
                     LOAD R4 0
                     SUB R4 [R5+13]
                     STOR R4 [R5+13]
                     LOAD R4 BELT
                     STOR R4 [R5+13]
                                                                  ;unset($temp)
                     LOAD R4 10                                   ;$state = 10
                     LOAD R5 -16                                  ;display($state, "leds2", "")
                     LOAD R4 R4
                     STOR R4 [R5+10]
                                                                  ;unset($startStop, $push, $state)
                     BRA motorUpTimer                             ;motorUpTimer()
                     
                                                                  ;if ($push == 1) {
conditional9:        LOAD R2 0                                    ;$temp = 0
                     STOR R2 [GB +outputs + HBRIDGE0]             ;_storeData($temp, 'outputs', HBRIDGE0)
                     LOAD R4 7                                    ;$state = 7
                     LOAD R5 -16                                  ;display($state, "leds2", "")
                     LOAD R4 R4
                     STOR R4 [R5+10]
                                                                  ;unset($push, $state)
                     BRA whiteWait                                ;whiteWait()
                     
whiteWait:           BRS timerManage                              ;timerManage()
                     CMP R1 SORT                                  ;if ($sleep == SORT) {
                     BEQ conditional10
return10:            PUSH R3                                      ;$startStop = _getButtonPressed(0)
                     LOAD R3 0
                     BRS _pressed
                     PULL R4
                     SUB SP 5
                     PULL R2
                     ADD SP 4
                     CMP R2 1                                     ;if ($startStop == 1) {
                     BEQ conditional11
return11:                                                         ;unset($startStop)
                     ADD R1 1                                     ;$sleep+=1
                     BRA whiteWait                                ;whiteWait()
                     
                                                                  ;if ($sleep == SORT) {
conditional10:       LOAD R2 9                                    ;$temp = 9
                     STOR R2 [GB +outputs + HBRIDGE1]             ;_storeData($temp, 'outputs', HBRIDGE1)
                     LOAD R4 8                                    ;$state = 8
                     LOAD R5 -16                                  ;display($state, "leds2", "")
                     LOAD R4 R4
                     STOR R4 [R5+10]
                     LOAD R1 0                                    ;$sleep = 0
                                                                  ;unset($state, $temp)
                     BRA motorDown                                ;motorDown()
                     
                                                                  ;if ($startStop == 1) {
conditional11:       LOAD R4 0                                    ;$temp = 0
                     STOR R4 [GB +outputs + FEEDERENGINE]         ;_storeData($temp, 'outputs', FEEDERENGINE)
                                                                  ;unset($temp)
                     LOAD R5 -16                                  ;setTimer(BELT)
                     LOAD R4 0
                     SUB R4 [R5+13]
                     STOR R4 [R5+13]
                     LOAD R4 BELT
                     STOR R4 [R5+13]
                     LOAD R4 11                                   ;$state = 11
                     LOAD R5 -16                                  ;display($state, "leds2", "")
                     LOAD R4 R4
                     STOR R4 [R5+10]
                     BRA whiteWaitTimer                           ;whiteWaitTimer()
                     
whiteWaitTimer:      BRS timerManage                              ;timerManage()
                     BRA whiteWaitStop                            ;whiteWaitStop()
                     
whiteWaitStop:       BRS timerManage                              ;timerManage()
                     CMP R1 SORT * 1000                           ;if ($sleep == SORT * 1000) {
                     BEQ conditional12
return12:            ADD R1 1                                     ;$sleep+=1
                     BRA whiteWait                                ;whiteWait()
                     
                                                                  ;if ($sleep == SORT * 1000) {
conditional12:       LOAD R2 9                                    ;$temp = 9
                     STOR R2 [GB +outputs + HBRIDGE1]             ;_storeData($temp, 'outputs', HBRIDGE1)
                     LOAD R4 12                                   ;$state = 12
                     LOAD R5 -16                                  ;display($state, "leds2", "")
                     LOAD R4 R4
                     STOR R4 [R5+10]
                     LOAD R1 0                                    ;$sleep = 0
                     BRA motorDownStop                            ;motorDownStop()
                                                                  ;unset($state)
                     BRA return12                                 ;}
                     
motorDownStop:       BRS timerManage                              ;timerManage()
                     CMP R1 TIMEMOTORDOWN                         ;if ($sleep == TIMEMOTORDOWN) {
                     BEQ conditional13
return13:            ADD R1 1                                     ;$sleep+=1
                     BRA motorDown                                ;motorDown()
                     
                                                                  ;if ($sleep == TIMEMOTORDOWN) {
conditional13:       LOAD R2 0                                    ;$temp = 0
                     STOR R2 [GB +outputs + HBRIDGE1]             ;_storeData($temp, 'outputs', HBRIDGE1)
                     LOAD R4 9                                    ;$state = 9
                     LOAD R1 0                                    ;$sleep = 0
                     LOAD R5 -16                                  ;display($state, "leds2", "")
                     LOAD R4 R4
                     STOR R4 [R5+10]
                                                                  ;unset($state)
                     BRA runningStop                              ;runningStop()
                     
runningStop:         BRS timerManage                              ;timerManage()
                     PUSH R3                                      ;$colour = _getButtonPressed(6)
                     LOAD R3 6
                     BRS _pressed
                     PULL R4
                     SUB SP 5
                     PULL R3
                     ADD SP 4
                     CMP R3 1                                     ;if ($colour == 1) {
                     BEQ conditional14
return14:            BRA runningStop                              ;runningStop()
                     BRA main
                     
                                                                  ;if ($colour == 1) {
conditional14:       LOAD R2 9                                    ;$temp = 9
                     STOR R2 [GB +outputs + HBRIDGE0]             ;_storeData($temp, 'outputs', HBRIDGE0)
                     LOAD R4 10                                   ;$state = 10
                     LOAD R5 -16                                  ;display($state, "leds2", "")
                     LOAD R4 R4
                     STOR R4 [R5+10]
                                                                  ;unset($colour, $state)
                     BRA motorUpStop                              ;motorUpStop()
                     
motorUpStop:         BRS timerManage                              ;timerManage()
                     PUSH R3                                      ;$push = _getButtonPressed(5)
                     LOAD R3 5
                     BRS _pressed
                     PULL R4
                     SUB SP 5
                     PULL R3
                     ADD SP 4
                     CMP R3 1                                     ;if ($push == 1) {
                     BEQ conditional15
return15:            BRA motorUpStop                              ;motorUpStop()
                     BRA main
                     
                                                                  ;if ($push == 1) {
conditional15:       LOAD R2 0                                    ;$temp = 0
                     STOR R2 [GB +outputs + HBRIDGE0]             ;_storeData($temp, 'outputs', HBRIDGE0)
                     LOAD R4 11                                   ;$state = 11
                     LOAD R5 -16                                  ;display($state, "leds2", "")
                     LOAD R4 R4
                     STOR R4 [R5+10]
                     BRA whiteWaitStop                            ;whiteWaitStop()
                                                                  ;unset($push, $state)
                     BRA return15                                 ;}
                     
motorDown:           BRS timerManage                              ;timerManage()
                     PUSH R3                                      ;$startStop = _getButtonPressed(0)
                     LOAD R3 0
                     BRS _pressed
                     PULL R4
                     SUB SP 5
                     PULL R3
                     ADD SP 4
                     CMP R1 TIMEMOTORDOWN                         ;if ($sleep == TIMEMOTORDOWN) {
                     BEQ conditional16
return16:            CMP R2 1                                     ;if ($startStop == 1) {
                     BEQ conditional17
return17:            ADD R1 1                                     ;$sleep+=1
                     BRA motorDown                                ;motorDown()
                     
                                                                  ;if ($sleep == TIMEMOTORDOWN) {
conditional16:       LOAD R2 0                                    ;$temp = 0
                     STOR R2 [GB +outputs + HBRIDGE1]             ;_storeData($temp, 'outputs', HBRIDGE1)
                     LOAD R4 9                                    ;$state = 9
                     LOAD R1 0                                    ;$sleep = 0
                     LOAD R5 -16                                  ;display($state, "leds2", "")
                     LOAD R4 R4
                     STOR R4 [R5+10]
                                                                  ;unset($state, $startStop, $temp)
                     BRA runningWait                              ;runningWait()
                     
                                                                  ;if ($startStop == 1) {
conditional17:       LOAD R3 0                                    ;$temp = 0
                     STOR R3 [GB +outputs + FEEDERENGINE]         ;_storeData($temp, 'outputs', FEEDERENGINE)
                     LOAD R5 -16                                  ;setTimer(BELT)
                     LOAD R4 0
                     SUB R4 [R5+13]
                     STOR R4 [R5+13]
                     LOAD R4 BELT
                     STOR R4 [R5+13]
                     LOAD R4 12                                   ;$state = 12
                     LOAD R5 -16                                  ;display($state, "leds2", "")
                     LOAD R4 R4
                     STOR R4 [R5+10]
                     BRA motorDownTimer                           ;motorDownTimer()
                                                                  ;unset($state, $startStop)
                     BRA return17                                 ;}
                     
motorDownTimer:      BRS timerManage                              ;timerManage()
                     BRA motorDownStop                            ;motorDownStop()
                     
motorUpTimer:        BRS timerManage                              ;timerManage()
                     BRA motorUpStop                              ;motorUpStop()
                     
runningTimerReset:   BRS timerManage                              ;timerManage()
                     BRA runningWait                              ;runningWait()
                     
runningTimer:        BRS timerManage                              ;timerManage()
                     BRA runningStop                              ;runningStop()
                     
timerManage:         MOD R0 12                                    ;mod(12, $counter)
                     ADD R2 outputs                               ;$temp = _getData('outputs', $location)
                     LOAD R3 [ GB + R2]
                     SUB R2 outputs
                     CMP R3 R0                                    ;if ($temp > $counter) {
                     BGT conditional18
return18:            CMP R2 7                                     ;if ($location > 7) {
                     BGT conditional19
return19:            ADD R2 1                                     ;$location+=1
                     BRA timerManage                              ;branch('timerManage')
                     
                                                                  ;if ($temp > $counter) {
conditional18:       LOAD R3 R2                                   ;$temp = $location
                     PUSH R4                                      ;$temp = pow(2, $temp)
                     PUSH R5
                     LOAD R4 R3
                     LOAD R5 2
                     BRS _pow
                     LOAD R3 R5
                     PULL R5
                     PULL R4
                     ADD R4 R3                                    ;$engines += $temp
                     BRA return18                                 ;}
                     
                                                                  ;if ($location > 7) {
conditional19:       LOAD R5 -16                                  ;display($engines, "leds", "")
                     LOAD R4 R4
                     STOR R4 [R5+11]
                     LOAD R4 0                                    ;$engines = 0
                     LOAD R2 0                                    ;$location = 0
                     ADD R0 1                                     ;$counter+=1
                     RTS                                          ;return
                     BRA return19                                 ;}
                     
                     @END