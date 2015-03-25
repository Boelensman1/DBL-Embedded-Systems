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
main:               LOAD R0 0                                    ;$counter=0
                    LOAD R1 0                                    ;$temp = 0
                    STOR R1 [GB +outputs + HBRIDGE1]             ;storeData($temp, 'outputs', HBRIDGE1)
                    STOR R1 [GB +outputs + LENSLAMPPOSITION]     ;storeData($temp, 'outputs', LENSLAMPPOSITION)
                    STOR R1 [GB +outputs + LENSLAMPSORTER]       ;storeData($temp, 'outputs', LENSLAMPSORTER)
                    STOR R1 [GB +outputs + LEDSTATEINDICATOR]    ;storeData($temp, 'outputs', LEDSTATEINDICATOR)
                    STOR R1 [GB +outputs + DISPLAY]              ;storeData($temp, 'outputs', DISPLAY)
                    STOR R1 [GB +outputs + CONVEYORBELT]         ;storeData($temp, 'outputs', CONVEYORBELT)
                    STOR R1 [GB +outputs + FEEDERENGINE]         ;storeData($temp, 'outputs', FEEDERENGINE)
                    LOAD R2 0                                    ;$state = 0
                    PUSH R5                                      ;display($state, "leds2", "")
                    LOAD R5 -16
                    STOR R2 [R5+10]
                    PULL R5
                    LOAD R1 9                                    ;$temp = 9
                    STOR R1 [GB +outputs + HBRIDGE0]             ;storeData($temp, 'outputs', HBRIDGE0)
                                                                 ;unset($temp, $state)
                    BRA setVars                                  ;setVars()
                    
setVars:            BRS timerManage                              ;timerManage()
                    LOAD R1 0                                    ;$temp = 0
                    STOR R1 [GB +outputs + HBRIDGE0]             ;storeData($temp, 'outputs', HBRIDGE0)
                    LOAD R1 12                                   ;$temp = 12
                    STOR R1 [GB +outputs + LENSLAMPPOSITION]     ;storeData($temp, 'outputs', LENSLAMPPOSITION)
                    STOR R1 [GB +outputs + LENSLAMPSORTER]       ;storeData($temp, 'outputs', LENSLAMPSORTER)
                    LOAD R1 9                                    ;$temp = 9
                    STOR R1 [GB +outputs + CONVEYORBELT]         ;storeData($temp, 'outputs', CONVEYORBELT)
                    LOAD R1 5                                    ;$temp = 5
                    STOR R1 [GB +outputs + FEEDERENGINE]         ;storeData($temp, 'outputs', FEEDERENGINE)
                    BRA test                                     ;test()
                    
test:               BRS timerManage                              ;timerManage()
                    BRA test                                     ;test()
                    
timerManage:        MOD R0 12                                    ;mod(12, $counter)
                    ADD R2 outputs                               ;$temp = getData('outputs', $location)
                    LOAD R1 [ GB + R2]
                    SUB R2 outputs
                    CMP R1 R0                                    ;if ($temp > $counter) {
                    BGT conditional0
return0:            CMP R2 7                                     ;if ($location > 7) {
                    BGT conditional1
return1:            ADD R2 1                                     ;$location+=1
                    BRA timerManage                              ;branch('timerManage')
                    
                                                                 ;if ($temp > $counter) {
conditional0:       LOAD R1 R2                                   ;$temp = $location
                    PUSH R4                                      ;$temp = pow(2, $temp)
                    PUSH R5
                    LOAD R4 R1
                    LOAD R5 2
                    BRS _pow
                    LOAD R1 R5
                    PULL R5
                    PULL R4
                    ADD R3 R1                                    ;$engines += $temp
                    BRA return0                                  ;}
                    
                                                                 ;if ($location > 7) {
conditional1:       PUSH R5                                      ;display($engines, "leds", "")
                    LOAD R5 -16
                    STOR R3 [R5+11]
                    PULL R5
                    LOAD R3 0                                    ;$engines = 0
                    LOAD R2 0                                    ;$location = 0
                    ADD R0 1                                     ;$counter+=1
                    RTS                                          ;return
                    BRA return1                                  ;}
                    
                    @END