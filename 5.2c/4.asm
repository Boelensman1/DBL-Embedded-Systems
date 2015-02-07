@DATA
intensity DS 7
counter DS 1

@CODE

WAIT EQU 1000
begin:	BRA main


;pow
_pow:   	CMP R4 0
            BEQ _pow1
            CMP R4 1
            BEQ _powR5
            PUSH R3
            PUSH R4
            SUB R4 1
			LOAD R3 R5
_powLoop:	MULS R5 R3
		 	SUB R4 1
			CMP R4 0
			BEQ _powReturn
			BRA _powLoop
_powReturn: PULL R4
            PULL R3
			RTS
_pow1:      LOAD R5 1
            RTS
_powR5:     RTS

;pressed
_pressed: 	LOAD R4 R3
            LOAD R5 2
            BRS _pow
            LOAD R3 R5
            LOAD R5 -16
            LOAD R4 [R5+7]
            DIV R4 R3
            MOD R4 2
            RTS
main: 		;Install timer
			                      LOAD  R0  loop
			                       ADD  R0  R5
			                      LOAD  R1  16
			                      STOR  R0  [R1]
			
			                      LOAD  R5  -16
			
			                      ; Set the timer to 0
			                      LOAD  R0  0
			                       SUB  R0  [R5+13]
			                      STOR  R0  [R5+13]
			LOAD R0 0
			LOAD R1 0
			LOAD R2 0
			STOR R1 [GB +counter + 0]
			BRA init
			BRA main

init: 		ADD R2 1
			ADD R2 intensity
			STOR R0 [ GB + R2]
			SUB R2 intensity
			CMP R2 7
			BEQ conditional0
return0:			BRA init
			BRA main

conditional0: 		LOAD R5 -16
			LOAD  R4  0
			SUB  R4  [R5+13]
			STOR  R4  [R5+13]
			LOAD R4 WAIT
			STOR R4 [R5+13]
			SETI  8
			BRA emptyLoop
			BRA return0
			BRA main

emptyLoop: 		BRA emptyLoop
			BRA main

loop: 		LOAD R5 -16
			LOAD  R4  0
			SUB  R4  [R5+13]
			STOR  R4  [R5+13]
			LOAD R4 WAIT
			STOR R4 [R5+13]
			SETI  8
			LOAD R1 [ GB + counter + 0 ]
			ADD R1 1
			STOR R1 [GB +counter + 0]
			CMP R1 10
			BEQ conditional1
return1:			LOAD R2 -1
			LOAD R3 0
			LOAD R4 0
			BRA getValues
			RTE

conditional1: 		LOAD R1 1
			BRA return1
			BRA main

getValues: 		ADD R2 1
			CMP R2 0
			BEQ conditional2
return2:			CMP R2 0
			BNE conditional3
return3:			CMP R1 R4
			BMI conditional4
return4:			ADD R2 intensity
			LOAD R4 [ GB + R2]
			SUB R2 intensity
			CMP R1 R4
			BEQ conditional5
return5:			CMP R2 7
			BEQ conditional6
return6:			BRA getValues
			BRA main

conditional2: 		LOAD  R5  -16
			LOAD R4 [R5 + 6]
			DIV R4 28
			ADD R4 1
			BRA return2
			BRA main

conditional3: 		ADD R2 intensity
			LOAD R4 [ GB + R2]
			SUB R2 intensity
			BRA return3
			BRA main

conditional4: 		LOAD R4 2
			PUSH R3
			LOAD R4 R2
			LOAD R5 R4
			BRS _pow
			PULL R3
			ADD R3 R5
			BRA return4
			BRA main

conditional5: 		LOAD R4 2
			PUSH R3
			LOAD R4 R2
			LOAD R5 R4
			BRS _pow
			PULL R3
			ADD R3 R5
			BRA return5
			BRA main

conditional6: 		LOAD  R5  -16
			LOAD R4 R3
			STOR R4 [R5+11]
			LOAD R2 0
			BRA checkButtons
			BRA return6
			BRA main

checkButtons: 		CMP R1 5
			BNE conditional7
return7:			ADD R2 1
			PUSH R3
			LOAD R3 R2
			BRS _pressed
			PULL R3
			CMP R4 1
			BEQ conditional8
return8:			CMP R2 7
			BNE conditional12
return12:			RTE
			BRA main

conditional7: 		RTE
			BRA return7
			BRA main

conditional8: 		ADD R2 intensity
			LOAD R4 [ GB + R2]
			SUB R2 intensity
			ADD R4 1
			PUSH R3
			LOAD R3 0
			BRS _pressed
			PULL R3
			CMP R4 1
			BEQ conditional9
return9:			CMP R4 10
			BNE conditional10
return10:			BRA return8
			BRA main

conditional9: 		SUB R4 2
			BRA return9
			BRA main

conditional10: 		CMP R4 -1
			BNE conditional11
return11:			BRA return10
			BRA main

conditional11: 		ADD R2 intensity
			STOR R4 [ GB + R2]
			SUB R2 intensity
			BRA return11
			BRA main

conditional12: 		BRA checkButtons
			BRA return12
			BRA main

@END