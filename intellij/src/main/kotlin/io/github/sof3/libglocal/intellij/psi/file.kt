package io.github.sof3.libglocal.intellij.psi

import com.intellij.extapi.psi.PsiFileBase
import com.intellij.psi.FileViewProvider
import com.intellij.psi.stubs.PsiFileStubImpl
import io.github.sof3.libglocal.intellij.LibglocalFileType
import io.github.sof3.libglocal.intellij.LibglocalLanguage

/*
 * libglocal
 *
 * Copyright 2018 SOFe
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

class LgcFile(vp: FileViewProvider) : PsiFileBase(vp, LibglocalLanguage) {
	override fun getFileType() = LibglocalFileType
}

class LgcFileStub(file: LgcFile) : PsiFileStubImpl<LgcFile>(file)
