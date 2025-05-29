.MODEL TINY
.386
.CODE
ORG 100h

start:
    mov ax, cs
    mov ds, ax

    mov dx, OFFSET msg1
    call print_string
    call wait_enter

    mov dx, OFFSET msg2
    call print_string
    call wait_enter

    mov dx, OFFSET msg3
    call print_string
    call wait_enter

    mov dx, OFFSET msg4
    call print_string
    call wait_enter

    mov dx, OFFSET msg5
    call print_string
    call wait_enter

    mov ax, 4C00h
    int 21h

print_string PROC
    mov ah, 09h
    int 21h
    ret
print_string ENDP

wait_enter PROC
wait_loop:
    mov ah, 01h
    int 21h
    cmp al, 13
    jne wait_loop
    ret
wait_enter ENDP

msg1 db 'Kurasa$'
msg2 db 'Ku sedang di mabuk cinta$'
msg3 db 'Nikmatnya$'
msg4 db 'Kini ku di mabuk cinta...$'
msg5 db '@unreliablecode$'

END start
